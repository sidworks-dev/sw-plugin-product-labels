<?php declare(strict_types=1);

namespace Sidworks\ProductLabels\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
class Migration1732810409SetupTable extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1732810409;
    }

    public function update(Connection $connection): void
    {
        $query = <<<SQL
        CREATE TABLE IF NOT EXISTS `sidworks_product_labels` (
            `id` BINARY(16) NOT NULL,
            `active` tinyint(3) unsigned NOT NULL DEFAULT 0,
            `label_name` VARCHAR(255),
            `label_content` VARCHAR(255),
            `label_background_color` VARCHAR(255),
            `label_text_color` VARCHAR(255),
            `product_stream_id` VARCHAR(16),
            `created_at` DATETIME(3) NOT NULL,
            `updated_at` DATETIME(3) NULL,
             PRIMARY KEY (`id`),
             CONSTRAINT `fk.sidworks_product_labels.product_stream_id` FOREIGN KEY (`product_stream_id`)
                REFERENCES `product_stream` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
         ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        SQL;

        $connection->executeStatement($query);
    }
}
