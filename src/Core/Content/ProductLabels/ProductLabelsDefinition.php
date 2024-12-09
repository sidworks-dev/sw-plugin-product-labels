<?php declare(strict_types=1);

namespace Sidworks\ProductLabels\Core\Content\ProductLabels;

use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslatedField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\TranslationsAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;
use Shopware\Core\Content\ProductStream\ProductStreamDefinition;
use Sidworks\ProductLabels\Core\Content\Aggregate\ProductLabelsTranslation\ProductLabelsTranslationDefinition;

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
                (new BoolField('active', 'active'))->addFlags(new Required()),
                (new TranslatedField('name'))->addFlags(new Required()),
                (new TranslatedField('content'))->addFlags(new Required()),
                (new StringField('background_color', 'backgroundColor'))->addFlags(new Required()),
                (new StringField('text_color', 'textColor'))->addFlags(new Required()),
                (new JsonField('sales_channels', 'salesChannelIds'))->addFlags(new Required()),
                (new FkField('product_stream_id', 'productStreamId', ProductStreamDefinition::class))->addFlags(new Required()),
                (new BoolField('from_to_active', 'fromToActive')),
                (new DateTimeField('from_date_time', 'fromDateTime')),
                (new DateTimeField('to_date_time', 'toDateTime')),
                (new TranslationsAssociationField(ProductLabelsTranslationDefinition::class, 'sidworks_product_labels_id'))
            ]
        );
    }
}
