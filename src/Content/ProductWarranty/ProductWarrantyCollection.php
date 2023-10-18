<?php

declare(strict_types=1);

namespace Sas\WarrantyManager\Content\ProductWarranty;

use Shopware\Core\Framework\DataAbstractionLayer\EntityCollection;

/**
 * @method void                  add(ProductWarrantyEntity $entity)
 * @method void                  set(string $key, ProductWarrantyEntity $entity)
 * @method ProductWarrantyEntity[]    getIterator()
 * @method ProductWarrantyEntity[]    getElements()
 * @method ProductWarrantyEntity|null get(string $key)
 * @method ProductWarrantyEntity|null first()
 * @method ProductWarrantyEntity|null last()
 */
class ProductWarrantyCollection extends EntityCollection
{
    public function getApiAlias(): string
    {
        return 'sas_warranty_manager_product';
    }

    protected function getExpectedClass(): string
    {
        return ProductWarrantyEntity::class;
    }
}
