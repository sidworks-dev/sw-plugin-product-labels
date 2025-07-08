<?php declare(strict_types=1);

namespace Sidworks\ProductLabels\Service;

use Shopware\Core\Content\ProductStream\Service\ProductStreamBuilderInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\NotFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Grouping\FieldGrouping;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Sorting\FieldSorting;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\System\SalesChannel\Entity\SalesChannelRepository;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsAnyFilter;
use Sidworks\ProductLabels\Core\Content\ProductLabels\ProductLabelsEntity;

class LabelStreamService
{
    private const SIDWORKS_PRODUCT_LABELS_EXTENSION = 'sidworksProductLabels';

    private array $labelCache = [];

    public function __construct(
        private readonly EntityRepository $productLabelsRepository,
        private readonly ProductStreamBuilderInterface $productStreamBuilder,
        private readonly SalesChannelRepository $productRepository
    ) {}

    public function getProductLabelStreamProducts(array $productIds, SalesChannelContext $context): array
    {
        $cacheKey = md5(implode(',', $productIds) . $context->getSalesChannelId());

        if (isset($this->labelCache[$cacheKey])) {
            return $this->labelCache[$cacheKey];
        }

        $productLabels = $this->fetchActiveProductLabels($context->getContext());

        $productLabelStreamItems = [];

        foreach ($productLabels as $productLabel) {
            if (!$this->shouldShowProductLabel($productLabel)) {
                continue;
            }

            $matchedIds = $this->getMatchedProductIds($productLabel, $productIds, $context);

            if (empty($matchedIds)) {
                continue;
            }

            $productLabelStreamItems[] = [
                'label' => $productLabel,
                'matchedProductIds' => array_values(array_unique($matchedIds)),
            ];
        }

        $this->labelCache[$cacheKey] = $productLabelStreamItems;

        return $productLabelStreamItems;
    }

    public function applyLabelsToProduct($product, array $productLabelsStreamProducts): void
    {
        foreach ($productLabelsStreamProducts as $productLabelsStreamProduct) {
            $matchedIds = $productLabelsStreamProduct['matchedProductIds'] ?? [];
            $label = $productLabelsStreamProduct['label'];
            $labelId = $label->getId();

            $matchedMap = array_flip($matchedIds);
            if (!isset($matchedMap[$product->getId()])) {
                continue;
            }

            /** @var ArrayEntity $sidworksProductLabels */
            $sidworksProductLabels = $product->getExtension(self::SIDWORKS_PRODUCT_LABELS_EXTENSION) ?? new ArrayEntity();
            $sidworksProductLabels->set($labelId, $label);

            $product->addExtension(self::SIDWORKS_PRODUCT_LABELS_EXTENSION, $sidworksProductLabels);
        }
    }

    private function fetchActiveProductLabels(Context $context): iterable
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('active', 1));
        $criteria->addFilter(new ContainsFilter('salesChannelIds', $context->getSource()->getSalesChannelId()));

        return $this->productLabelsRepository->search($criteria, $context)->getEntities();
    }

    private function getMatchedProductIds(ProductLabelsEntity $productLabel, array $productIds, SalesChannelContext $context): array
    {
        $matchedIds = [];

        $selectedProducts = $productLabel->getSelectedProducts() ?? [];
        $selectedMatches = array_intersect($productIds, $selectedProducts);
        if (!empty($selectedMatches)) {
            $matchedIds = array_flip($selectedMatches);
        }

        $filters = null;
        if ($productLabel->getProductStreamId()) {
            $filters = $this->productStreamBuilder->buildFilters(
                $productLabel->getProductStreamId(),
                $context->getContext()
            );

            $productIds[] = '18e3f68e6de24264b6e837098d16ac1c';
            $criteria = new Criteria($productIds);
            $criteria->addFilter(...$filters);
            $criteria->addFields(['id']);

            $streamProducts = $this->productRepository->search($criteria, $context);
            $streamProductIds = $streamProducts->getEntities()->getIds();

            foreach ($streamProductIds as $id) {
                $matchedIds[$id] = true;
            }
        }

        if ($this->shouldProcessVariants($selectedProducts, $productLabel->getProductStreamId())) {
            $variantToParent = $this->getVariantToParentMapping($productIds, $context);

            if (!empty($variantToParent)) {
                if (!empty($selectedProducts)) {
                    $variantMatches = array_intersect(array_keys($variantToParent), $selectedProducts);
                    foreach ($variantMatches as $variantId) {
                        $matchedIds[$variantToParent[$variantId]] = true;
                    }
                }

                if ($filters !== null) {
                    $variantIds = array_keys($variantToParent);
                    $variantStreamCriteria = new Criteria($variantIds);
                    $variantStreamCriteria->addFilter(...$filters);
                    $variantStreamCriteria->addFields(['id']);

                    $variantStreamProducts = $this->productRepository->search($variantStreamCriteria, $context);

                    foreach ($variantStreamProducts->getIds() as $variantId) {
                        $matchedIds[$variantToParent[$variantId]] = true;
                    }
                }
            }
        }

        return array_keys($matchedIds);
    }

    private function shouldProcessVariants(array $selectedProducts, ?string $productStreamId): bool
    {
        return !empty($selectedProducts) || $productStreamId !== null;
    }

    private function getVariantToParentMapping(array $productIds, SalesChannelContext $context): array
    {
        $variantCriteria = new Criteria();
        $variantCriteria->addFilter(new EqualsAnyFilter('parentId', $productIds));
        $variantCriteria->addFields(['id', 'parentId']);

        $variantProducts = $this->productRepository->search($variantCriteria, $context);

        if ($variantProducts->count() === 0) {
            return [];
        }

        $variantToParent = [];
        foreach ($variantProducts->getEntities() as $variant) {
            $variantId = $variant->get('id');
            $parentId = $variant->get('parentId');
            $variantToParent[$variantId] = $parentId;
        }

        return $variantToParent;
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
            $from && $from <= $now && (!$to || $to >= $now) => true,
            default => $productLabel->getActive()
        };
    }
}
