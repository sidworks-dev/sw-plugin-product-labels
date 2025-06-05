<?php

namespace Sidworks\ProductLabels\Event;

use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\ShopwareSalesChannelEvent;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\Event;
use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductEntity;

class ProductSliderProductsLoadedEvent extends Event implements ShopwareSalesChannelEvent
{
    public function __construct(
        private readonly SalesChannelProductCollection $products,
        private readonly string $slotId,
        protected SalesChannelContext $context
    ) {}

    /**
     * @return SalesChannelProductEntity[]
     */
    public function getProducts(): SalesChannelProductCollection
    {
        return $this->products;
    }

    public function getSlotId(): string
    {
        return $this->slotId;
    }

    public function getContext(): Context
    {
        return $this->context->getContext();
    }

    public function getSalesChannelContext(): SalesChannelContext
    {
        return $this->context;
    }
}
