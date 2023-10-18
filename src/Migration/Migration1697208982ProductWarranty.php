<?php

declare(strict_types=1);

namespace Sas\WarrantyManager\Migration;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Exception;
use Sas\WarrantyManager\Content\ProductWarranty\WarrantyImportStatics;
use Shopware\Core\Content\ImportExport\ImportExportProfileEntity;
use Shopware\Core\Content\ImportExport\ImportExportProfileTranslationDefinition;
use Shopware\Core\Defaults;
use Shopware\Core\Framework\Migration\MigrationStep;
use Shopware\Core\Framework\Uuid\Uuid;
use Shopware\Core\Migration\Traits\ImportTranslationsTrait;
use Shopware\Core\Migration\Traits\Translations;

class Migration1697208982ProductWarranty extends MigrationStep
{
    use ImportTranslationsTrait;

    public function getCreationTimestamp(): int
    {
        return 1697208982;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement(
            '
            CREATE TABLE IF NOT EXISTS `sas_warranty_manager_product` (
                `id` BINARY(16) NOT NULL,
                `product_id` BINARY(16) NULL,
                `customer_id` BINARY(16) NULL,
                `warranty_duration` INT(11) NULL,
                `warranty_text` LONGTEXT NULL,
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                CONSTRAINT `fk.sas_warranty_manager_product.product_id` FOREIGN KEY (`product_id`) REFERENCES `product` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
                CONSTRAINT `fk.sas_warranty_manager_product.customer_id` FOREIGN KEY (`customer_id`) REFERENCES `customer` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
                PRIMARY KEY (`id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        '
        );
        $this->insertWarrantyProfile($connection);
    }

    private function insertWarrantyProfile(Connection $connection): void
    {
        $id = Uuid::randomBytes();
        $profileId = $this->profileId($connection);

        if (! empty($profileId)) {
            return;
        }
        // we need to add import-export because we will export failed rows in case of exception

        $connection->insert(
            'import_export_profile', [
            'id' => $id,
            'name' => WarrantyImportStatics::PROFILE_NAME,
            'system_default' => 1,
            // we need to prevent user to modify or remove this profile, that's why its 1
            'source_entity' => 'sas_warranty_manager_product',
            'file_type' => 'text/csv',
            'delimiter' => ',',
            'enclosure' => '"',
            'type' => ImportExportProfileEntity::TYPE_IMPORT_EXPORT,
            'mapping' => json_encode([]),
            'created_at' => (new \DateTime())->format(Defaults::STORAGE_DATE_TIME_FORMAT),
            ]
        );

        $translations = new Translations(
            [
                'import_export_profile_id' => $id,
                'label' => 'Warranty Importprofil',
            ],
            [
                'import_export_profile_id' => $id,
                'label' => 'Warranty import profile',
            ]
        );

        $this->importTranslation(ImportExportProfileTranslationDefinition::ENTITY_NAME, $translations, $connection);
    }

    private function profileId(Connection $connection): ?string
    {
        try {
            return (string) $connection->fetchOne(
                'SELECT id FROM `import_export_profile` WHERE `name` = :name',
                [
                    'name' => WarrantyImportStatics::PROFILE_NAME,
                ]
            );
        } catch (Exception $e) {
            return null;
        }
    }

    public function updateDestructive(Connection $connection): void
    {
        // implement update destructive
    }
}
