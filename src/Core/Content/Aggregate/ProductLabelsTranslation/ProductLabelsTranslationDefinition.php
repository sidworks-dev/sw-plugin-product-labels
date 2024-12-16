<?php declare(strict_types=1);

namespace Sidworks\ProductLabels\Core\Content\Aggregate\ProductLabelsTranslation;

use Sidworks\ProductLabels\Core\Content\ProductLabels\ProductLabelsDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\{EntityTranslationDefinition,
    Field\Flag\AllowHtml,
    Field\Flag\ApiAware,
    Field\Flag\Required,
    Field\StringField,
    FieldCollection};

class ProductLabelsTranslationDefinition extends EntityTranslationDefinition
{

    public function getEntityName(): string
    {
        return 'sidworks_product_labels_translation';
    }

    public function getCollectionClass(): string
    {
        return ProductLabelsTranslationCollection::class;
    }

    public function getEntityClass(): string
    {
        return ProductLabelsTranslationEntity::class;
    }

    public function getParentDefinitionClass(): string
    {
        return ProductLabelsDefinition::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection([
            (new StringField('name', 'name', 255))->addFlags(new ApiAware(), new Required()),
            (new StringField('content', 'content', 255))->addFlags(new ApiAware(), new Required()),
        ]);
    }

}
