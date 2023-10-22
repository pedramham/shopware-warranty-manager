<?php

namespace WarrantyManager\Tests\Command;

use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionClass;
use Sas\WarrantyManager\Command\ImportWarrantyProduct;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\Test\TestCaseBase\IntegrationTestBehaviour;

class ImportWarrantyProductTest extends TestCase
{
    use IntegrationTestBehaviour;

    private EntityRepository $productRepository;
    private EntityRepository $customerRepository;
    private EntityRepository $warrantyManagerProductRepository;
    private LoggerInterface $loggerMock;
    private ImportWarrantyProduct $mportWarrantyProduct;

    protected function setUp(): void
    {

        $this->productRepository = $this->getContainer()->get('product.repository');
        $this->customerRepository = $this->getContainer()->get('customer.repository');
        $this->warrantyManagerProductRepository = $this->getContainer()->get('sas_warranty_manager_product.repository');
        $this->loggerMock = $this->getMockBuilder(LoggerInterface::class)->getMock();


        $this->importWarrantyProduct = new ImportWarrantyProduct(
            $this->productRepository,
            $this->customerRepository,
            $this->warrantyManagerProductRepository,
            $this->loggerMock
        );
    }

    public function testImportWarranty(): void
    {
        $this->loggerMock->expects($this->never())
            ->method('warning')
            ->with('ImportWarrantyProduct: File dose not exists: /path/to/file/that/does/not/exist.csv');

        $mockImportWarrantyProduct = $this->getMockBuilder(ImportWarrantyProduct::class)
            ->setConstructorArgs([
                    $this->productRepository,
                    $this->customerRepository,
                    $this->warrantyManagerProductRepository,
                    $this->loggerMock
                ]
            )
            ->getMock();


        $mockImportWarrantyProduct->expects($this->once())->method('importWarranty')->willReturn(null);
        $this->assertNull($mockImportWarrantyProduct->importWarranty());

    }

    public function testImportWarrantyIsNull(): void
    {
        $reflection = new ReflectionClass(ImportWarrantyProduct::class);
        $method = $reflection->getMethod('importWarranty');
        $result = $method->invokeArgs($this->importWarrantyProduct, []);
        $this->assertNull($result);

    }


}