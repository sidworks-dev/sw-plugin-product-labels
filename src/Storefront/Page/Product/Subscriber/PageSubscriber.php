<?php declare(strict_types=1);

namespace Sidworks\ProductLabels\Storefront\Page\Product\Subscriber;

use Shopware\Core\Framework\Struct\ArrayEntity;
use Shopware\Core\Content\Product\Events\ProductListingResultEvent;
use Shopware\Core\Content\Product\Events\ProductSearchResultEvent;
use Sidworks\ProductLabels\Service\LabelStreamService;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class PageSubscriber implements EventSubscriberInterface
{
    public function __construct(
      private LabelStreamService $labelStreamService
    ) {
    }

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
        $productLabelsStreamProducts = $this->labelStreamService->getProductLabelStreamProducts($context);

        if (empty($productLabelsStreamProducts)) {
            return;
        }

        $productEntities = $event->getResult()->getEntities();

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
}
