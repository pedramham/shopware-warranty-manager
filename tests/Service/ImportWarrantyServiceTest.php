<?php

namespace WarrantyManager\Tests\Service;

use PHPUnit\Framework\TestCase;
use Sas\WarrantyManager\Command\ImportWarrantyProduct;
use Sas\WarrantyManager\Service\ImportWarrantyService;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;

class ImportWarrantyServiceTest extends TestCase
{
    use IntegrationTestBehaviour;

    private EntityRepository $productRepository;
    private EntityRepository $customerRepository;
    private EntityRepository $warrantyManagerProductRepository;
    private ImportWarrantyProduct $mportWarrantyProduct;

    protected function setUp(): void
    {
        $this->productRepository = $this->getContainer()->get('product.repository');
        $this->customerRepository = $this->getContainer()->get('customer.repository');
        $this->warrantyManagerProductRepository = $this->getContainer()->get('sas_warranty_manager_product.repository');


        $this->importWarrantyProduct = new ImportWarrantyService(
            $this->productRepository,
            $this->customerRepository,
            $this->warrantyManagerProductRepository,
        );
    }

    public function testImportWarranty(): void
    {

        $mockImportWarrantyProduct = $this->getMockBuilder(ImportWarrantyService::class)
            ->setConstructorArgs([
                    $this->productRepository,
                    $this->customerRepository,
                    $this->warrantyManagerProductRepository,
                ]
            )
            ->getMock();

        $context = Context::createDefaultContext();
        $mockImportWarrantyProduct->expects($this->once())->method('importWarranty')->with($context,[])->willReturn(false);
        $this->assertFalse($mockImportWarrantyProduct->importWarranty($context,[]));

    }
    public function testImportWarranty2(): void
    {

        $context = Context::createDefaultContext();
        $arrayProductWarranty = ['SWDEMO10007', 'SWDEMO10000', 'warranty1', 1];

        // Test case when all parameters are valid
        $result = $this->importWarrantyProduct->importWarranty($context, $arrayProductWarranty);
        $this->assertTrue($result);

        // Test case when productNumber is empty
        $arrayProductWarranty = ['', 'SWDEMO10000', 'warranty1', 1];
        $result = $this->importWarrantyProduct->importWarranty($context, $arrayProductWarranty);
        $this->assertFalse($result);

        // Test case when customerNumber is empty
        $arrayProductWarranty = ['SWDEMO10007', '', 'warranty1', 1];
        $result = $this->importWarrantyProduct->importWarranty($context, $arrayProductWarranty);
        $this->assertFalse($result);

        // Test case when warrantyDuration is empty
        $arrayProductWarranty = ['SWDEMO10007', 'SWDEMO10000', 'warranty1', null];
        $result = $this->importWarrantyProduct->importWarranty($context, $arrayProductWarranty);
        $this->assertFalse($result);
    }

}