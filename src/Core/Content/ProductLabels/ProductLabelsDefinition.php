<?php declare(strict_types=1);

namespace Sidworks\ProductLabels\Core\Content\ProductLabels;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Content\ProductStream\ProductStreamDefinition;

class ProductLabelsDefinition extends EntityDefinition
{
    public function getEntityName(): string
    {
        return 'sidworks_product_labels';
    }

    public function getEntityClass(): string
    {
        return ProductLabelsEntity::class;
    }

    public function getCollectionClass(): string
    {
        return ProductLabelsCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection(
            [
                (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey()),
                new BoolField('active', 'active'),
                new StringField('name', 'name'),
                new StringField('content', 'content'),
                new StringField('background_color', 'backgroundColor'),
                new StringField('text_color', 'textColor'),
                (new FkField('product_stream_id', 'productStreamId', ProductStreamDefinition::class))->addFlags(new Required())
            ]
        );
    }
}
