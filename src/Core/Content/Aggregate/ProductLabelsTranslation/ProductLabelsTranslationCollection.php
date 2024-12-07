<?php declare(strict_types=1);

namespace Sidworks\ProductLabels\Core\Content\Aggregate\ProductLabelsTranslation;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

class ProductLabelsTranslationCollection extends EntityCollection {

    protected function getExpectedClass(): string
    {
        return ProductLabelsTranslationEntity::class;
    }
}
