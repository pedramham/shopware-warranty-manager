<?php

declare(strict_types=1);

namespace Sas\WarrantyManager\Service;

use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class ImportWarrantyService
{
    public const FILE_PATH_NAME = __DIR__ . '/../../data/warranty-product.csv';

    private EntityRepository $productRepository;

    private EntityRepository $customerRepository;

    private EntityRepository $warrantyManagerProductRepository;

    public function __construct(EntityRepository $productRepository, EntityRepository $customerRepository, EntityRepository $warrantyManagerProductRepository)
    {
        $this->productRepository = $productRepository;
        $this->customerRepository = $customerRepository;
        $this->warrantyManagerProductRepository = $warrantyManagerProductRepository;
    }

    public function importWarranty(Context $context, array $arrayProductWarranty): bool
    {
        $productNumber = $this->getProductId($context, $arrayProductWarranty[0]);
        $customerNumber = $this->getCustomerId($context, $arrayProductWarranty[1]);

        if (! empty($productNumber) && ! empty($customerNumber) && ! empty($arrayProductWarranty[3])) {
            $this->warrantyManagerProductRepository->create(
                [
                    [
                        'product' => [
                            'id' => $productNumber,
                        ],
                        'customer' => [
                            'id' => $customerNumber,
                        ],
                        'warrantyText' => $arrayProductWarranty[2] ?? null,
                        'warrantyDuration' => (int) $arrayProductWarranty[3],
                    ],
                ],
                $context
            );
            return true;
        }

        return false;
    }

    private function getProductId(Context $context, ?string $productNumber): ?string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('productNumber', $productNumber));
        return $this->productRepository->searchIds($criteria, $context)->firstId();
    }

    private function getCustomerId(Context $context, ?string $customerNumber): ?string
    {
        $criteria = new Criteria();
        $criteria->addFilter(new EqualsFilter('customerNumber', $customerNumber));

        return $this->customerRepository->searchIds($criteria, $context)->firstId();
    }
}
