<?php

declare(strict_types=1);

namespace Sas\WarrantyManager\Content\Decorator;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use League\Flysystem\FilesystemException;
use League\Flysystem\FilesystemOperator;
use Psr\Log\LoggerInterface;
use Sas\WarrantyManager\Service\ImportWarrantyService;
use Sas\WarrantyManager\Trait\CallParentPrivateTrait;
use Shopware\Core\Content\ImportExport\Aggregate\ImportExportFile\ImportExportFileEntity;
use Shopware\Core\Content\ImportExport\Aggregate\ImportExportLog\ImportExportLogEntity;
use Shopware\Core\Content\ImportExport\Exception\FileNotFoundException;
use Shopware\Core\Content\ImportExport\ImportExport;
use Shopware\Core\Content\ImportExport\Processing\Pipe\AbstractPipe;
use Shopware\Core\Content\ImportExport\Processing\Reader\AbstractReader;
use Shopware\Core\Content\ImportExport\Processing\Writer\AbstractWriter;
use Shopware\Core\Content\ImportExport\Service\AbstractFileService;
use Shopware\Core\Content\ImportExport\Service\ImportExportService;
use Shopware\Core\Content\ImportExport\Struct\Config;
use Shopware\Core\Content\ImportExport\Struct\Progress;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Write\Validation\WriteCommandExceptionEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Throwable;

/**
 * This class is used to import warranty that are provided as a custom .csv file
 */
class WarrantyManager extends ImportExport
{
    use CallParentPrivateTrait;

    public function __construct(
        private readonly ImportExportService $importExportService,
        private readonly ImportExportLogEntity $logEntity,
        private readonly FilesystemOperator $filesystem,
        private readonly EventDispatcherInterface $eventDispatcher,
        private readonly Connection $connection,
        private readonly EntityRepository $repository,
        private readonly AbstractPipe $pipe,
        private readonly AbstractReader $reader,
        private readonly AbstractWriter $writer,
        private readonly AbstractFileService $fileService,
        private readonly int $importLimit = 250,
        private readonly int $exportLimit = 250,
        private readonly LoggerInterface $logger,
        private readonly ImportWarrantyService $importWarrantyService,
    ) {
        parent::__construct(
            $importExportService,
            $logEntity,
            $filesystem,
            $eventDispatcher,
            $connection,
            $repository,
            $pipe,
            $reader,
            $writer,
            $fileService,
            $importLimit,
            $exportLimit
        );
    }

    /**
     * @throws Throwable
     * @throws FilesystemException
     * @throws Exception
     */
    public function import(Context $context, int $offset = 0): Progress
    {
        $progress = $this->importExportService->getProgress($this->logEntity->getId(), $offset);

        if (! $this->logEntity->getFile() instanceof ImportExportFileEntity) {
            return $progress;
        }

        $fileSize = $this->logEntity->getFile()->getSize();
        $progress->setTotal($fileSize);

        if ($progress->isFinished()) {
            return $progress;
        }

        $path = $this->logEntity->getFile()->getPath();
        $progress->setTotal($fileSize);

        // Import and create CSV rows
        $this->importCsvRows(
            $path,
            $offset,
            $context,
            $progress
        );
        return $progress;
    }

    /* Import and create CSV rows
     * The file CSV should consist of Product Number, Customer Number, Warranty text, Warranty_duration
     *   SWDEMO10007,SWDEMO10000,"test text for product SWDEMO10007",11
     *   SWDEMO10000,SWDEMO10001,"test text for product SWDEMO10000",11
     *   SWDEMO10001,SWDEMO10000,"test text for product SWDEMO10001",11
     *   ...
    */

    /**
     * @throws FilesystemException
     * @throws Exception
     * @throws Throwable
     */
    private function importCsvRows(
        string $path,
        int $offset,
        Context $context,
        Progress $progress
    ): array {
        $invalidRecordsProgress = null;
        $failedRecords = [];
        $overallResults = [];
        $processed = 0;

        try {
            $resource = $this->filesystem->readStream($path);
        } catch (FileNotFoundException $exception) {
            throw new FileNotFoundException($path);
        }

        $config = Config::fromLog($this->logEntity);
        //create an array for import to the table
        $warrantiesArray = $this->makeWarrantiesArray($this->reader->read($config, $resource, $offset));

        foreach ($warrantiesArray as $row) {
            if ($this->logEntity->getActivity() === ImportExportLogEntity::ACTIVITY_DRYRUN) {
                $this->connection->setNestTransactionsWithSavepoints(true);
                $this->connection->beginTransaction();
            }

            if (! $this->importWarrantyService->importWarranty($context, $row)) {
                $this->logger->warning('This Warranty with this specification ,productNumber: ' . $row[0] . 'customerNumber :' . $row[1] . ' was not entered. because it is not in the database .');
                $record['_error'] = 'This Warranty with this specification ,productNumber: ' . $row[0] . 'customerNumber :' . $row[1] . ' was not entered. because it is not in the database .';
                $failedRecords[] = $record;
            }

            $progress->addProcessedRecords(1);

            if ($this->logEntity->getActivity() === ImportExportLogEntity::ACTIVITY_DRYRUN) {
                $this->connection->rollBack();
            }

            $this->importExportService->saveProgress($progress);
            $overallResults = $this->logEntity->getResult();
            ++$processed;
            if ($this->importLimit > 0 && $processed >= $this->importLimit) {
                break;
            }
        }

        $progress->setOffset($this->reader->getOffset());

        $this->eventDispatcher->removeListener(WriteCommandExceptionEvent::class, $this->onWriteException(...));

        if (! empty($failedRecords)) {
            $invalidRecordsProgress = self::callPrivateMethod(
                $this,
                'exportInvalid',
                [$context, $failedRecords]
            );

            $progress->setInvalidRecordsLogId($invalidRecordsProgress->getLogId());
        }

        // importing the file is complete
        if ($this->reader->getOffset() === $this->filesystem->fileSize($path)) {
            if ($this->logEntity->getInvalidRecordsLog() instanceof ImportExportLogEntity) {
                /**
                 * @var ImportExportLogEntity $invalidLog
                 */
                $invalidLog = $this->logEntity->getInvalidRecordsLog();
                $invalidRecordsProgress ??= $this->importExportService->getProgress($invalidLog->getId(), $invalidLog->getRecords());

                // complete invalid records export
                self::callPrivateMethod(
                    $this,
                    'mergePartFiles',
                    [$this->logEntity->getInvalidRecordsLog(), $invalidRecordsProgress]
                );

                $invalidRecordsProgress->setState(Progress::STATE_SUCCEEDED);
                $this->importExportService->saveProgress($invalidRecordsProgress);
            }

            $progress->setState($invalidRecordsProgress === null ? Progress::STATE_SUCCEEDED : Progress::STATE_FAILED);
        }

        $this->importExportService->saveProgress($progress, $overallResults);
        return [$invalidRecordsProgress, $failedRecords, $overallResults];
    }

    /* We create an array consisting of the required data about the warranty. (According to the CSV file)
         *   0 => array:3 [
         *       0 => "SWDEMO10000"
         *       1 => "SWDEMO10000"
         *       2 => "text warranty"
         *       3 => 2
         *   ],
         *   1 => array:3 [
         *       0 => "SWDEMO10020"
         *       1 => "SWDEMO10030"
         *       2 => "text warranty"
         *       3 => 5
         *   ],
         *   .....
     */
    private function makeWarrantiesArray(iterable $csvRows): array
    {
        $arrayWarranties = null;
        $result = [];

        foreach ($csvRows as $row) {
            $arrayWarranties[] = array_values($row);
        }

        foreach ($arrayWarranties as $value) {
            if (preg_match('~[0-9]+~', $value[0])) {
                $result[] = explode(';', trim($value[0]));
            }
        }

        return $result;
    }
}
