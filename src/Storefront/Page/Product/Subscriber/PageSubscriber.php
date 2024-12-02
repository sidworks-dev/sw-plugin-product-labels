<?php declare(strict_types=1);

namespace Sidworks\ProductLabels\Storefront\Page\Product\Subscriber;

use Shopware\Core\Framework\Struct\ArrayEntity;
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
            ProductPageLoadedEvent::class => 'onProductPageLoadedResult',
            ProductListingResultEvent::class => 'onProductListingResult',
            ProductSearchResultEvent::class => 'onProductListingResult'
        ];
    }

    public function onProductPageLoadedResult(ProductPageLoadedEvent $event): void
    {
        $context = $event->getContext();
        $productLabelsStreamProducts = $this->labelStreamService->getProductLabelStreamProducts($context);

        if (empty($productLabelsStreamProducts)) {
            return;
        }

        $product = $event->getPage()->getProduct();

        $this->labelStreamService->applyLabelsToProduct($product, $productLabelsStreamProducts);
    }

    public function onProductListingResult(ProductListingResultEvent $event): void
    {
        $context = $event->getContext();
        $productLabelsStreamProducts = $this->labelStreamService->getProductLabelStreamProducts($context);

        if (empty($productLabelsStreamProducts)) {
            return;
        }

        foreach ($event->getResult()->getEntities() as $productEntity) {
            $this->labelStreamService->applyLabelsToProduct($productEntity, $productLabelsStreamProducts);
        }
    }
}
