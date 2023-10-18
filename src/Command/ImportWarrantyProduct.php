<?php

declare(strict_types=1);

namespace Sas\WarrantyManager\Command;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

class ImportWarrantyProduct
{
    public const FILE_PATH_NAME = __DIR__ . '/../../data/warranty-product.csv';

    private EntityRepository $productRepository;

    private EntityRepository $customerRepository;

    private EntityRepository $warrantyManagerProductRepository;

    private LoggerInterface $logger;

    public function __construct(EntityRepository $productRepository, EntityRepository $customerRepository, EntityRepository $warrantyManagerProductRepository, LoggerInterface $logger)
    {
        $this->productRepository = $productRepository;
        $this->customerRepository = $customerRepository;
        $this->warrantyManagerProductRepository = $warrantyManagerProductRepository;
        $this->logger = $logger;
    }

    public function importWarranty()
    {
        $context = Context::createDefaultContext();
        $arrayProductWarranties = $this->getCsvFile();

        if ($arrayProductWarranties === null) {
            return;
        }

        foreach ($arrayProductWarranties as $arrayProductWarranty) {
            $productNumber = $this->getProductId($context, $arrayProductWarranty[0]);
            $customerNumber = $this->getCustomerId($context, $arrayProductWarranty[1]);

            if (! empty($productNumber) && ! empty($customerNumber) && ! empty($arrayProductWarranty[3])) {
                $this->createWarrantyManagerProduct(
                    $context,
                    $productNumber,
                    $customerNumber,
                    $arrayProductWarranty[2] ?? null,
                    (int) $arrayProductWarranty[3]
                );
            } else {
                $this->logger->warning('This Warranty with this specification ,productNumber: ' . $productNumber . 'customerNumber :' . $customerNumber . ' was not entered. because it is not in the store .  ');
            }
        }
    }

    private function getCsvFile(): ?array
    {
        if (! file_exists(ImportWarrantyProduct::FILE_PATH_NAME)) {
            $this->logger->warning('ImportWarrantyProduct: File dose not exists: ' . ImportWarrantyProduct::FILE_PATH_NAME);

            return null;
        }

        return array_filter(
            array_map(
                function ($value) {
                    return preg_match('~[0-9]+~', $value) ? explode(';', trim($value)) : null;
                },
                file(self::FILE_PATH_NAME)
            )
        );
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

    private function createWarrantyManagerProduct(Context $context, $productId, $customer, ?string $warrantyText, int $warrantyDuration): void
    {
        $this->warrantyManagerProductRepository->create(
            [
                [
                    'product' => [
                        'id' => $productId,
                    ],
                    'customer' => [
                        'id' => $customer,
                    ],
                    'warrantyDuration' => $warrantyDuration,
                    'warrantyText' => $warrantyText,
                ],
            ],
            $context
        );
    }
}
