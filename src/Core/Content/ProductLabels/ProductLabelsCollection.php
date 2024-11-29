<?php declare(strict_types=1);

namespace Sidworks\ProductLabels\Core\Content\ProductLabels;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class ProductLabelsCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ProductLabelsEntity::class;
    }
}
