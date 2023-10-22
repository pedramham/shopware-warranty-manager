<?php

declare(strict_types=1);

namespace Sas\WarrantyManager\Content\ProductWarranty;

use Shopware\Core\Checkout\Customer\CustomerCollection;
use Shopware\Core\Checkout\Customer\CustomerEntity;
use Shopware\Core\Content\Product\ProductCollection;
use Shopware\Core\Content\Product\ProductDefinition;
use Shopware\Core\Content\Product\ProductEntity;
use Shopware\Core\Framework\DataAbstractionLayer\Entity;
use Shopware\Core\Framework\DataAbstractionLayer\EntityCustomFieldsTrait;
use Shopware\Core\Framework\DataAbstractionLayer\EntityIdTrait;
use Shopware\Core\Framework\DataAbstractionLayer\Field\ManyToManyAssociationField;
use Shopware\Core\Framework\DataAbstractionLayer\FieldCollection;

class ProductWarrantyEntity extends Entity
{
    use EntityIdTrait;

    use EntityCustomFieldsTrait;

    protected ?int $warrantyDuration;

    protected ?string $warrantyText;

    protected ProductEntity $product;

    protected CustomerEntity $customer;

    public function setWarrantyDuration(?int $warrantyDuration): void
    {
        $this->warrantyDuration = $warrantyDuration;
    }

    public function getWarrantyDuration(): ?int
    {
        return $this->warrantyDuration;
    }

    public function setWarrantyText(?string $warrantyText): void
    {
        $this->warrantyText = $warrantyText;
    }

    public function getWarrantyText(): ?string
    {
        return $this->warrantyText;
    }

    public function getProduct(): ProductEntity
    {
        return $this->product;
    }

    public function setProduct(ProductEntity $product): void
    {
        $this->product = $product;
    }

    public function getCustomer(): CustomerEntity
    {
        return $this->customer;
    }

    public function setCustomer(CustomerEntity $customer): void
    {
        $this->customer = $customer;
    }
}
