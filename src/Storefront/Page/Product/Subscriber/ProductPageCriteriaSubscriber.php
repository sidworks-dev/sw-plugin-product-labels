<?php declare(strict_types=1);

namespace Sidworks\ProductLabels\Storefront\Page\Product\Subscriber;

use Shopware\Core\Content\ProductStream\Service\ProductStreamBuilderInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\Content\Product\Events\ProductListingResultEvent;
use Shopware\Core\Content\Product\Events\ProductSearchResultEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class ProductPageCriteriaSubscriber implements EventSubscriberInterface
{
    public function __construct(
        private readonly EntityRepository $productLabelsRepository,
        private readonly ProductStreamBuilderInterface $productStreamBuilder,
        private readonly EntityRepository $productRepository
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ProductListingResultEvent::class => 'onProductListingResult',
            ProductSearchResultEvent::class => 'onProductListingResult',
        ];
    }

    public function onProductListingResult(ProductListingResultEvent $event): void
    {
        $context = $event->getContext();
        $productEntities = $event->getResult()->getEntities();
        $productIds = $productEntities->getIds();

        $productLabelsStreamProducts = $this->getProductLabelStreamProducts($context, $productIds);

        if (empty($productLabelsStreamProducts)) {
            return;
        }

        foreach ($productLabelsStreamProducts as $productLabelsStreamProduct) {
            $streamProducts = $productLabelsStreamProduct['stream']->getIds();
            $label = $productLabelsStreamProduct['label'];
            $labelId = $label->getId();

            foreach ($productEntities as $productEntity) {
                if (!in_array($productEntity->getId(), $streamProducts, true)) {
                    continue;
                }

                $sidworksProductLabels = $productEntity->getExtension('sidworksProductLabels') ?? new ArrayEntity();
                $sidworksProductLabels->set($labelId, $label);

                $productEntity->addExtension('sidworksProductLabels', $sidworksProductLabels);
            }
        }
    }

    /**
     * Retrieve product label stream products.
     *
     * @param Context $context
     * @param array|null $productIds
     * @return array
     */
    private function getProductLabelStreamProducts($context, ?array $productIds = null): array
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

            $productStreamCriteria = $productIds ? new Criteria($productIds) : new Criteria();
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
