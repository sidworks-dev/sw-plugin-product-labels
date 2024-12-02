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
        $productLabelsStreamProducts = $this->fetchLabelStreamProducts($event->getContext());
        if (empty($productLabelsStreamProducts)) {
            return;
        }

        $product = $event->getPage()->getProduct();
        $this->labelStreamService->applyLabelsToProduct($product, $productLabelsStreamProducts);
    }

    public function onProductListingEvent(ProductListingResultEvent $event): void
    {
        $productLabelsStreamProducts = $this->fetchLabelStreamProducts($event->getContext());
        if (empty($productLabelsStreamProducts)) {
            return;
        }

        $this->applyLabelsToProducts($event->getResult()->getEntities(), $productLabelsStreamProducts);
    }

    public function onProductCrossSellingsLoaded(ProductCrossSellingsLoadedEvent $event): void
    {
        $productLabelsStreamProducts = $this->fetchLabelStreamProducts($event->getContext());
        if (empty($productLabelsStreamProducts)) {
            return;
        }

        foreach ($event->getCrossSellings() as $crossSellEntity) {
            $this->applyLabelsToProducts($crossSellEntity->getProducts(), $productLabelsStreamProducts);
        }
    }

    private function fetchLabelStreamProducts($context): array
    {
        return $this->labelStreamService->getProductLabelStreamProducts($context);
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
