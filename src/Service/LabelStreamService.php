<?php declare(strict_types=1);

namespace Sidworks\ProductLabels\Service;

use Shopware\Core\Content\ProductStream\Service\ProductStreamBuilderInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class LabelStreamService
{
    public function __construct(
        private readonly EntityRepository $productLabelsRepository,
        private readonly ProductStreamBuilderInterface $productStreamBuilder,
        private readonly EntityRepository $productRepository
    ) {}

    public function getProductLabelStreamProducts(Context $context): array
    {
        $productLabelsCriteria = new Criteria();
        $productLabelsCriteria->addFilter(new EqualsFilter('active', 1));

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
}
