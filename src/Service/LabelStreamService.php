<?php declare(strict_types=1);

namespace Sidworks\ProductLabels\Service;

use Shopware\Core\Content\ProductStream\Service\ProductStreamBuilderInterface;
use Shopware\Core\Framework\Adapter\Cache\CacheValueCompressor;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Sidworks\ProductLabels\Core\Content\ProductLabels\ProductLabelsEntity;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;

class LabelStreamService
{
    private const SIDWORKS_PRODUCT_LABELS_EXTENSION = 'sidworksProductLabels';
    private const CACHE_TTL = 3600; // 1 hour
    private const CACHE_TAG_PREFIX = 'sidworks-product-labels';

    private array $labelCache = [];
    private array $variantMappingCache = [];
    private array $streamMatchCache = [];

    public function __construct(
        private readonly EntityRepository $productLabelsRepository,
        private readonly ProductStreamBuilderInterface $productStreamBuilder,
        private readonly SalesChannelRepository $productRepository,
        private readonly CacheInterface $cache
    ) {}

    public function getProductLabelStreamProducts(array $productIds, SalesChannelContext $context): array
    {
        $languageId = $context->getLanguageId();

        // Check request-scoped cache first
        $requestCacheKey = $this->getCacheKey($productIds, $context->getSalesChannelId(), $languageId);
        if (isset($this->labelCache[$requestCacheKey])) {
            return $this->labelCache[$requestCacheKey];
        }

        // Check persistent cache
        $persistentCacheKey = $this->getPersistentCacheKey($productIds, $context->getSalesChannelId(), $languageId);

        $result = $this->cache->get($persistentCacheKey, function (ItemInterface $item) use ($productIds, $context) {
            $item->expiresAfter(self::CACHE_TTL);
            $item->tag([
                self::CACHE_TAG_PREFIX,
                self::CACHE_TAG_PREFIX . '-sales-channel-' . $context->getSalesChannelId()
            ]);

            return $this->computeProductLabelStreamProducts($productIds, $context);
        });

        // Store in request-scoped cache for subsequent calls in same request
        $this->labelCache[$requestCacheKey] = $result;

        return $result;
    }

    private function computeProductLabelStreamProducts(array $productIds, SalesChannelContext $context): array
    {
        $productLabels = $this->fetchActiveProductLabels($context->getContext());
        $productLabelStreamItems = [];

        if (!$productLabels) {
            return [];
        }

        // Pre-fetch variant mapping once for all labels
        $variantToParent = $this->getVariantToParentMapping($productIds, $context);

        foreach ($productLabels as $productLabel) {
            if (!$this->shouldShowProductLabel($productLabel)) {
                continue;
            }

            $matchedIds = $this->getMatchedProductIds($productLabel, $productIds, $context, $variantToParent);
            if (empty($matchedIds)) {
                continue;
            }

            $productLabelStreamItems[] = [
                'label' => $productLabel,
                'matchedProductIds' => $matchedIds,
            ];
        }

        return $productLabelStreamItems;
    }

    public function applyLabelsToProduct($product, array $productLabelsStreamProducts): void
    {
        $productId = $product->getId();
        $sidworksProductLabels = $product->getExtension(self::SIDWORKS_PRODUCT_LABELS_EXTENSION) ?? new ArrayEntity();

        foreach ($productLabelsStreamProducts as $productLabelsStreamProduct) {
            if (!in_array($productId, $productLabelsStreamProduct['matchedProductIds'], true)) {
                continue;
            }

            $label = $productLabelsStreamProduct['label'];
            $sidworksProductLabels->set($label->getId(), $label);
        }

        if ($sidworksProductLabels->all()) {
            $product->addExtension(self::SIDWORKS_PRODUCT_LABELS_EXTENSION, $sidworksProductLabels);
        }
    }

    private function fetchActiveProductLabels(Context $context): iterable
    {
        $salesChannelId = $context->getSource()->getSalesChannelId();
        $languageId = $context->getLanguageId();
        $cacheKey = "active_labels_{$salesChannelId}_{$languageId}";

        if (isset($this->labelCache[$cacheKey])) {
            return $this->labelCache[$cacheKey];
        }

        // Use persistent cache for active labels
        $persistentCacheKey = self::CACHE_TAG_PREFIX . '-active-labels-' . $salesChannelId . '-' . $languageId;

        $result = $this->cache->get($persistentCacheKey, function (ItemInterface $item) use ($salesChannelId, $context) {
            $item->expiresAfter(self::CACHE_TTL);
            $item->tag([
                self::CACHE_TAG_PREFIX,
                self::CACHE_TAG_PREFIX . '-sales-channel-' . $salesChannelId
            ]);

            $criteria = new Criteria();
            $criteria->addFilter(new EqualsFilter('active', 1));
            $criteria->addFilter(new ContainsFilter('salesChannelIds', $salesChannelId));

            // Note: Not using addFields() here as it returns PartialEntity which causes type issues
            // The caching provides much better performance gains than field selection

            return $this->productLabelsRepository->search($criteria, $context)->getEntities();
        });

        $this->labelCache[$cacheKey] = $result;

        return $result;
    }

    private function getMatchedProductIds(
        ProductLabelsEntity $productLabel,
        array $productIds,
        SalesChannelContext $context,
        array $variantToParent
    ): array {
        $matchedIds = [];

        // Handle selected products
        if ($selectedProducts = $productLabel->getSelectedProducts()) {
            $matchedIds = array_flip(array_intersect($productIds, $selectedProducts));
        }

        // Handle product stream
        if ($streamId = $productLabel->getProductStreamId()) {
            $streamMatches = $this->matchProductsByStream($streamId, $productIds, $context);
            $matchedIds = array_merge($matchedIds, $streamMatches);
        }

        // Handle variants
        if ($this->shouldProcessVariants($selectedProducts, $productLabel->getProductStreamId())) {
            $variantMatches = $this->processVariantMatches($selectedProducts, $streamId, $variantToParent, $context);
            $matchedIds = array_merge($matchedIds, $variantMatches);
        }

        return array_keys($matchedIds);
    }

    private function processVariantMatches(
        ?array $selectedProducts,
        ?string $streamId,
        array $variantToParent,
        SalesChannelContext $context
    ): array {
        $matchedIds = [];

        if (!empty($selectedProducts)) {
            foreach (array_intersect(array_keys($variantToParent), $selectedProducts) as $variantId) {
                $matchedIds[$variantToParent[$variantId]] = true;
            }
        }

        if ($streamId && !empty($variantToParent)) {
            $streamVariantMatches = $this->matchVariantsByStream($streamId, array_keys($variantToParent), $variantToParent, $context);
            $matchedIds = array_merge($matchedIds, $streamVariantMatches);
        }

        return $matchedIds;
    }

    private function matchProductsByStream(string $streamId, array $productIds, SalesChannelContext $context): array
    {
        $cacheKey = $this->getStreamCacheKey($streamId, $productIds, $context->getSalesChannelId(), $context->getLanguageId());

        if (isset($this->streamMatchCache[$cacheKey])) {
            return $this->streamMatchCache[$cacheKey];
        }

        $filters = $this->productStreamBuilder->buildFilters($streamId, $context->getContext());
        $criteria = new Criteria($this->ensureMinProductIds($productIds));
        $criteria->addFilter(...$filters)->addFields(['id']);

        $matchedIds = [];
        foreach ($this->productRepository->search($criteria, $context)->getIds() as $id) {
            $matchedIds[$id] = true;
        }

        $this->streamMatchCache[$cacheKey] = $matchedIds;
        return $matchedIds;
    }

    private function matchVariantsByStream(string $streamId, array $variantIds, array $variantToParent, SalesChannelContext $context): array
    {
        $cacheKey = $this->getStreamCacheKey($streamId, $variantIds, $context->getSalesChannelId(), $context->getLanguageId()) . '_variants';

        if (isset($this->streamMatchCache[$cacheKey])) {
            return $this->streamMatchCache[$cacheKey];
        }

        $filters = $this->productStreamBuilder->buildFilters($streamId, $context->getContext());
        $criteria = new Criteria($variantIds);
        $criteria->addFilter(...$filters)->addFields(['id']);

        $matchedIds = [];
        foreach ($this->productRepository->search($criteria, $context)->getIds() as $variantId) {
            if (isset($variantToParent[$variantId])) {
                $matchedIds[$variantToParent[$variantId]] = true;
            }
        }

        $this->streamMatchCache[$cacheKey] = $matchedIds;
        return $matchedIds;
    }

    private function shouldProcessVariants(?array $selectedProducts, ?string $productStreamId): bool
    {
        return !empty($selectedProducts) || $productStreamId !== null;
    }

    private function getVariantToParentMapping(array $productIds, SalesChannelContext $context): array
    {
        if (empty($productIds)) {
            return [];
        }

        $cacheKey = $this->getCacheKey($productIds, $context->getSalesChannelId(), $context->getLanguageId()) . '_variants';

        if (isset($this->variantMappingCache[$cacheKey])) {
            return $this->variantMappingCache[$cacheKey];
        }

        $criteria = new Criteria();
        $criteria->addFilter(new EqualsAnyFilter('parentId', $productIds));
        $criteria->addFields(['id', 'parentId']);

        $variantProducts = $this->productRepository->search($criteria, $context);

        $mapping = [];
        foreach ($variantProducts->getEntities() as $variant) {
            $mapping[$variant->get('id')] = $variant->get('parentId');
        }

        $this->variantMappingCache[$cacheKey] = $mapping;
        return $mapping;
    }

    private function ensureMinProductIds(array $productIds): array
    {
        return count($productIds) === 1
            ? array_merge($productIds, ['00000000000000000000000000000000'])
            : $productIds;
    }

    public function shouldShowProductLabel(ProductLabelsEntity $productLabel): bool
    {
        if (!$productLabel->getFromToActive()) {
            return $productLabel->getActive();
        }

        $now = new \DateTimeImmutable();
        $from = $productLabel->getFromDateTime();
        $to = $productLabel->getToDateTime();

        return match (true) {
            $from && $from > $now => false,
            $to && $to < $now => false,
            default => true,
        };
    }

    private function getCacheKey(array $productIds, string $salesChannelId, string $languageId): string
    {
        sort($productIds); // Ensure consistent ordering for cache keys
        return md5(implode(',', $productIds) . $salesChannelId . $languageId);
    }

    private function getPersistentCacheKey(array $productIds, string $salesChannelId, string $languageId): string
    {
        sort($productIds);
        return self::CACHE_TAG_PREFIX . '-products-' . md5(implode(',', $productIds) . $salesChannelId . $languageId);
    }

    private function getStreamCacheKey(string $streamId, array $productIds, string $salesChannelId, string $languageId): string
    {
        sort($productIds);
        return md5($streamId . implode(',', $productIds) . $salesChannelId . $languageId);
    }

    /**
     * Invalidate all product label caches
     */
    public function invalidateCache(?string $salesChannelId = null): void
    {
        if ($salesChannelId) {
            $this->cache->invalidateTags([
                self::CACHE_TAG_PREFIX . '-sales-channel-' . $salesChannelId
            ]);
        } else {
            $this->cache->invalidateTags([self::CACHE_TAG_PREFIX]);
        }

        // Clear request-scoped caches
        $this->labelCache = [];
        $this->variantMappingCache = [];
        $this->streamMatchCache = [];
    }
}
