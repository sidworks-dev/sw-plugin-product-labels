<?php declare(strict_types=1);

namespace Sidworks\ProductLabels\Core\Content\ProductLabels;

use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\BoolField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\DateTimeField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\CascadeDelete;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\JsonField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
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
                (new IdField('id', 'id'))->addFlags(new ApiAware(), new Required(), new PrimaryKey()),
                (new BoolField('active', 'active'))->addFlags(new ApiAware(), new Required()),
                (new TranslatedField('name'))->addFlags(new ApiAware(), new Required()),
                (new TranslatedField('content'))->addFlags(new ApiAware(), new Required()),
                (new StringField('background_color', 'backgroundColor'))->addFlags(new ApiAware(), new Required()),
                (new StringField('text_color', 'textColor'))->addFlags(new ApiAware(), new Required()),
                (new JsonField('sales_channels', 'salesChannelIds'))->addFlags(new ApiAware(), new Required()),
                (new JsonField('selected_products', 'selectedProducts'))->addFlags(new ApiAware()),

                (new FkField('product_stream_id', 'productStreamId', ProductStreamDefinition::class))->addFlags(new ApiAware()),

                (new BoolField('from_to_active', 'fromToActive')),
                (new DateTimeField('from_date_time', 'fromDateTime')),
                (new DateTimeField('to_date_time', 'toDateTime')),
                (new TranslationsAssociationField(ProductLabelsTranslationDefinition::class, 'sidworks_product_labels_id'))
            ]
        );
    }
}
