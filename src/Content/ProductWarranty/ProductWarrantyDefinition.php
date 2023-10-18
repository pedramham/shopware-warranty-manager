<?php

declare(strict_types=1);

namespace Sas\WarrantyManager\Content\ProductWarranty;

use Sas\WarrantyManager\Content\Aggregate\ProductMappingDefinition;
use Shopware\Core\Checkout\Customer\CustomerDefinition;
use Shopware\Core\Content\Media\MediaDefinition;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\EntityDefinition;
use Shopware\Core\Framework\DataAbstractionLayer\Field\FkField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\ApiAware;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\PrimaryKey;
use Shopware\Core\Framework\DataAbstractionLayer\Field\Flag\Required;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IdField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\IntField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\OneToOneAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\Field\StringField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ProductWarrantyDefinition extends EntityDefinition
{
    public const ENTITY_NAME = 'sas_warranty_manager_product';

    public function getEntityName(): string
    {
        return self::ENTITY_NAME;
    }

    public function getEntityClass(): string
    {
        return ProductWarrantyEntity::class;
    }

    // Change this to return the correct class name for your entity collection
    public function getCollectionClass(): string
    {
        return ProductWarrantyCollection::class;
    }

    protected function defineFields(): FieldCollection
    {
        return new FieldCollection(
            [
                (new IdField('id', 'id'))->addFlags(new Required(), new PrimaryKey(), new ApiAware()),
                new FkField('product_id', 'productId', ProductDefinition::class),
                new FkField('customer_id', 'customerId', CustomerDefinition::class),
                (new IntField('warranty_duration', 'warrantyDuration'))->addFlags(new Required()),
                (new StringField('warranty_text', 'warrantyText'))->addFlags(new Required()),
                (new OneToOneAssociationField('product', 'product_id', 'id', ProductDefinition::class, true))->addFlags(new Required()),
                (new OneToOneAssociationField('customer', 'customer_id', 'id', CustomerDefinition::class, true))->addFlags(new Required()),

            ]
        );
    }
}
