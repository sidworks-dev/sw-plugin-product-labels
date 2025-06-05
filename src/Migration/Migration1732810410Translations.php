<?php declare(strict_types=1);

namespace Sidworks\ProductLabels\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
class Migration1732810410Translations extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1732810410;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
            CREATE TABLE IF NOT EXISTS `sidworks_product_labels_translation` (
                `sidworks_product_labels_id` BINARY(16) NOT NULL,
                `language_id` BINARY(16) NOT NULL,
                `name` VARCHAR(255),
                `content` VARCHAR(255),
                `created_at` DATETIME(3) NOT NULL,
                `updated_at` DATETIME(3) NULL,
                PRIMARY KEY (`sidworks_product_labels_id`, `language_id`),
                CONSTRAINT `fk.properties_translations.sidworks_product_labels_id` 
                    FOREIGN KEY (`sidworks_product_labels_id`)
                    REFERENCES `sidworks_product_labels` (`id`) 
                        ON DELETE CASCADE 
                        ON UPDATE CASCADE,
                CONSTRAINT `fk.sidworks_product_labels_properties_translations.language_id` 
                    FOREIGN KEY (`language_id`)
                    REFERENCES `language` (`id`) 
                        ON DELETE CASCADE 
                        ON UPDATE CASCADE
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ');
    }
}
