<?php

namespace Sidworks\ProductLabels\Event;

use Shopware\Core\Content\Product\SalesChannel\SalesChannelProductCollection;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\Event\ShopwareSalesChannelEvent;
use Shopware\Core\System\SalesChannel\SalesChannelContext;
use Symfony\Contracts\EventDispatcher\Event;

class ProductSliderProductsLoadedEvent extends Event implements ShopwareSalesChannelEvent
{
    public function __construct(
        private readonly SalesChannelProductCollection $products,
        protected SalesChannelContext $context
    ) {}

    public function getProducts(): SalesChannelProductCollection
    {
        return $this->products;
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
