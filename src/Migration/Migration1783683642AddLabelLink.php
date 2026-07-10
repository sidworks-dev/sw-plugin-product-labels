<?php declare(strict_types=1);

namespace Sidworks\ProductLabels\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
class Migration1783683642AddLabelLink extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1783683642;
    }

    public function update(Connection $connection): void
    {
        if ($this->linkColumnExists($connection)) {
            $connection->executeStatement('
                ALTER TABLE `sidworks_product_labels_translation`
                MODIFY COLUMN `link` LONGTEXT COLLATE utf8mb4_unicode_ci NULL
            ');

            return;
        }

        $connection->executeStatement('
            ALTER TABLE `sidworks_product_labels_translation`
            ADD COLUMN `link` LONGTEXT COLLATE utf8mb4_unicode_ci NULL AFTER `content`
        ');
    }

    private function linkColumnExists(Connection $connection): bool
    {
        return (bool) $connection->fetchOne('
            SELECT 1
            FROM information_schema.COLUMNS
            WHERE TABLE_SCHEMA = DATABASE()
                AND TABLE_NAME = :table
                AND COLUMN_NAME = :column
        ', [
            'table' => 'sidworks_product_labels_translation',
            'column' => 'link',
        ]);
    }
}
