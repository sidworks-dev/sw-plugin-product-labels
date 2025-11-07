<?php declare(strict_types=1);

namespace Sidworks\ProductLabels\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

class Migration1747000001AddPerformanceIndexes extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1747000001;
    }

    public function update(Connection $connection): void
    {
        $connection->executeStatement('
                ALTER TABLE `sidworks_product_labels`
                ADD INDEX `idx_active` (`active`)
        ');

        $connection->executeStatement('
                ALTER TABLE `sidworks_product_labels`
                ADD INDEX `idx_from_to_active` (`from_to_active`)
        ');
    }
}
