<?php

declare(strict_types=1);

namespace Sas\WarrantyManager\Content\Decorator;

use Doctrine\DBAL\Connection;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use Sas\WarrantyManager\Content\ProductWarranty\WarrantyImportStatics;
use Sas\WarrantyManager\Service\ImportWarrantyService;
use Sas\WarrantyManager\Trait\CallParentPrivateTrait;
use Shopware\Core\Content\ImportExport\Aggregate\ImportExportLog\ImportExportLogEntity;
use Shopware\Core\Content\ImportExport\ImportExport;
use Shopware\Core\Content\ImportExport\ImportExportFactory;
use Shopware\Core\Content\ImportExport\Service\AbstractFileService;
use Shopware\Core\Content\ImportExport\Service\ImportExportService;
use Shopware\Core\Framework\DataAbstractionLayer\DefinitionInstanceRegistry;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

/**
 * When users import a CSV file from the Import/Export administration panel,
 * this class has a duty to create an import/export class in the creation method.
 */
class ImportExportFactoryDecorator extends ImportExportFactory
{
    use CallParentPrivateTrait;

    public function __construct(
        private readonly ImportExportFactory $decorated,
        private readonly ImportExportService $importExportService,
        private readonly DefinitionInstanceRegistry $definitionInstanceRegistry,
        private readonly FilesystemOperator $filesystem,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly EntityRepository $logRepository,
        private readonly Connection $connection,
        private readonly AbstractFileService $fileService,
        private readonly \IteratorAggregate $readerFactories,
        private readonly \IteratorAggregate $writerFactories,
        private readonly \IteratorAggregate $pipeFactories,
        private readonly LoggerInterface $logger,
        private readonly ImportWarrantyService $importWarrantyService,
    ) {
        parent::__construct(
            $importExportService,
            $definitionInstanceRegistry,
            $filesystem,
            $eventDispatcher,
            $logRepository,
            $connection,
            $fileService,
            $readerFactories,
            $writerFactories,
            $pipeFactories,
        );
    }

    public function create(string $logId, int $importBatchSize = 250, int $exportBatchSize = 250): ImportExport
    {
        /**
 * @var ImportExportLogEntity $logEntity 
*/
        $logEntity = self::callPrivateMethod($this, 'findLog', [$logId]);

        // If the user select 'Warranty import profile' in admin panel
        if ($logEntity->getProfileName() === WarrantyImportStatics::PROFILE_NAME) {
            return new WarrantyManager(
                $this->importExportService,
                $logEntity,
                $this->filesystem,
                $this->eventDispatcher,
                $this->connection,
                self::callPrivateMethod($this, 'getRepository', [$logEntity]),
                self::callPrivateMethod($this, 'getPipe', [$logEntity]),
                self::callPrivateMethod($this, 'getReader', [$logEntity]),
                self::callPrivateMethod($this, 'getWriter', [$logEntity]),
                $this->fileService,
                $importBatchSize,
                $exportBatchSize,
                $this->logger,
                $this->importWarrantyService,
            );
        }

        return $this->decorated->create($logId, $importBatchSize, $exportBatchSize);
    }
}
