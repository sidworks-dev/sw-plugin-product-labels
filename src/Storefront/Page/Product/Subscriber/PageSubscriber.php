<?php declare(strict_types=1);

namespace Sidworks\ProductLabels\Storefront\Page\Product\Subscriber;

use Shopware\Core\Content\Product\Events\ProductCrossSellingsLoadedEvent;
use Shopware\Core\Content\Product\Events\ProductListingResultEvent;
use Shopware\Core\Content\Product\Events\ProductSearchResultEvent;
use Shopware\Storefront\Page\Product\ProductPageLoadedEvent;
use Sidworks\ProductLabels\Service\LabelStreamService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PageSubscriber implements EventSubscriberInterface
{

    public $hasSet = false;

    public function __construct(
        private readonly LabelStreamService $labelStreamService
    ) {}

    public static function getSubscribedEvents(): array
    {
        return [
            ProductPageLoadedEvent::class => 'onProductPageLoaded',
            ProductListingResultEvent::class => 'onProductListingEvent',
            ProductCrossSellingsLoadedEvent::class => 'onProductCrossSellingsLoaded',
            ProductSearchResultEvent::class => 'onProductListingEvent',
        ];
    }

    public function onProductPageLoaded(ProductPageLoadedEvent $event): void
    {
        $product = $event->getPage()->getProduct();

        $productLabelsStreamProducts = $this->fetchLabelStreamProducts([$product->getId()], $event->getContext());
        if (empty($productLabelsStreamProducts)) {
            return;
        }
        
        $this->labelStreamService->applyLabelsToProduct($product, $productLabelsStreamProducts);
    }

    public function onProductListingEvent(ProductListingResultEvent $event): void
    {
        $entities = $event->getResult()->getEntities();

        if ($entities->count() === 0) {
            return;
        }

        $productLabelsStreamProducts = $this->fetchLabelStreamProducts($entities->getIds(), $event->getContext());
        if (empty($productLabelsStreamProducts)) {
            return;
        }

        $this->applyLabelsToProducts($entities, $productLabelsStreamProducts);
    }

    public function onProductCrossSellingsLoaded(ProductCrossSellingsLoadedEvent $event): void
    {
        $entityIds = [];
        foreach ($event->getCrossSellings() as $crossSell) {
            $entityIds = array_merge($entityIds, $crossSell->getProducts()->getIds());
        }

        if (!$entityIds) {
            return;
        }

        $productLabelsStreamProducts = $this->fetchLabelStreamProducts($entityIds, $event->getContext());
        if (empty($productLabelsStreamProducts)) {
            return;
        }

        foreach ($event->getCrossSellings() as $crossSellEntity) {
            $this->applyLabelsToProducts($crossSellEntity->getProducts(), $productLabelsStreamProducts);
        }
    }

    private function fetchLabelStreamProducts($productIds, $context): array
    {
        return $this->labelStreamService->getProductLabelStreamProducts($productIds, $context);
    }

    private function applyLabelsToProducts($productEntities, array $productLabelsStreamProducts): void
    {
        if ($productEntities->count() === 0) {
            return;
        }

        foreach ($productEntities as $productEntity) {
            $this->labelStreamService->applyLabelsToProduct($productEntity, $productLabelsStreamProducts);
        }
    }
}
