<?php declare(strict_types=1);

namespace Sidworks\ProductLabels\Core\Content\ProductLabels;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void              add(ScriptsEntity $entity)
 * @method void              set(string $key, ScriptsEntity $entity)
 * @method ScriptsEntity[]    getIterator()
 * @method ScriptsEntity[]    getElements()
 * @method ScriptsEntity|null get(string $key)
 * @method ScriptsEntity|null first()
 * @method ScriptsEntity|null last()
 */
class ProductLabelsCollection extends EntityCollection
{
    protected function getExpectedClass(): string
    {
        return ProductLabelsEntity::class;
    }
}
