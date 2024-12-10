<?php declare(strict_types=1);

namespace Sidworks\ProductLabels\Service;

use Shopware\Core\Content\ProductStream\Service\ProductStreamBuilderInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\ContainsFilter;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Struct\ArrayEntity;

class LabelStreamService
{
    private const SIDWORKS_PRODUCT_LABELS_EXTENSION = 'sidworksProductLabels';

    public function __construct(
        private readonly EntityRepository $productLabelsRepository,
        private readonly ProductStreamBuilderInterface $productStreamBuilder,
        private readonly EntityRepository $productRepository
    ) {}

    public function getProductLabelStreamProducts(Context $context): array
    {
        $productLabelsCriteria = new Criteria();
        $productLabelsCriteria->addFilter(new EqualsFilter('active', 1));
        $productLabelsCriteria->addFilter(new ContainsFilter('salesChannelIds', $context->getSource()->getSalesChannelId()));

        $productLabels = $this->productLabelsRepository
            ->search($productLabelsCriteria, $context)
            ->getEntities();

        $productLabelStreamItems = [];
        foreach ($productLabels as $productLabel) {
            $productStreamFilters = $this->productStreamBuilder->buildFilters(
                $productLabel->getProductStreamId(),
                $context
            );

            $productStreamCriteria = new Criteria();
            $productStreamCriteria->addFilter(...$productStreamFilters);

            $streamProducts = $this->productRepository->search($productStreamCriteria, $context);

            $productLabelStreamItems[] = [
                'stream' => $streamProducts,
                'label' => $productLabel,
            ];
        }

        return $productLabelStreamItems;
    }

    public function applyLabelsToProduct($product, array $productLabelsStreamProducts): void
    {
        foreach ($productLabelsStreamProducts as $productLabelsStreamProduct) {
            $streamProducts = $productLabelsStreamProduct['stream']->getIds();
            $label = $productLabelsStreamProduct['label'];
            $labelId = $label->getId();

            if (!in_array($product->getId(), $streamProducts, true)) {
                continue;
            }

            /** @var ArrayEntity $sidworksProductLabels */
            $sidworksProductLabels = $product->getExtension(self::SIDWORKS_PRODUCT_LABELS_EXTENSION) ?? new ArrayEntity();
            $sidworksProductLabels->set($labelId, $label);

            $product->addExtension(self::SIDWORKS_PRODUCT_LABELS_EXTENSION, $sidworksProductLabels);
        }
    }
    public function shouldShowLabel($productLabel): bool
    {
        $now = new \DateTimeImmutable();
        $fromDateTime = $productLabel->getFromDateTime();
        $toDateTime = $productLabel->getToDateTime();

        return match (true) {
            $fromDateTime && $fromDateTime > $now => false,
            $toDateTime && $toDateTime < $now => false,
            $fromDateTime && $fromDateTime <= $now && (!$toDateTime || $toDateTime >= $now) => true,
            default => $productLabel->getActive()
        };
    }
}
