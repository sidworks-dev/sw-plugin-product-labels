<?php
declare(strict_types=1);

namespace Sidworks\ProductLabels\Decorator;

use Shopware\Core\Content\Cms\Aggregate\CmsSlot\CmsSlotEntity;
use Shopware\Core\Content\Cms\DataResolver\Element\CmsElementResolverInterface;
use Shopware\Core\Content\Cms\DataResolver\Element\ElementDataCollection;
use Shopware\Core\Content\Cms\DataResolver\ResolverContext\ResolverContext;
use Shopware\Core\Content\Cms\DataResolver\CriteriaCollection;
use Shopware\Core\Content\Product\Cms\ProductSliderCmsElementResolver;
use Sidworks\ProductLabels\Event\ProductSliderProductsLoadedEvent;
use Sidworks\ProductLabels\Service\LabelStreamService;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

class ProductSliderCmsElementResolverDecorator extends ProductSliderCmsElementResolver implements CmsElementResolverInterface
{
    private CmsElementResolverInterface $parent;

    public function __construct(
        CmsElementResolverInterface $parent,
        private readonly LabelStreamService $labelStreamService,
        private readonly EventDispatcherInterface $eventDispatcher
    ) {
        $this->parent = $parent;
    }

    public function collect(CmsSlotEntity $slot, ResolverContext $resolverContext): ?CriteriaCollection
    {
        return $this->parent->collect($slot, $resolverContext);
    }

    public function enrich(CmsSlotEntity $slot, ResolverContext $resolverContext, ElementDataCollection $result): void
    {
        $this->parent->enrich($slot, $resolverContext, $result);

        $products = $slot->getData()->getProducts();
        if ($products->count() === 0) {
            return;
        }

        $this->eventDispatcher->dispatch(
            new ProductSliderProductsLoadedEvent(
                $products,
                $resolverContext->getSalesChannelContext()
            )
        );
    }
}
