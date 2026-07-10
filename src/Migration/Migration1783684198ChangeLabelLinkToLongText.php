<?php declare(strict_types=1);

namespace Sidworks\ProductLabels\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
class Migration1783684198ChangeLabelLinkToLongText extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1783684198;
    }

    public function update(Connection $connection): void
    {
        if (!$this->columnExists($connection, 'sidworks_product_labels_translation', 'link')) {
            return;
        }

        $connection->executeStatement('
            ALTER TABLE `sidworks_product_labels_translation`
            MODIFY COLUMN `link` LONGTEXT COLLATE utf8mb4_unicode_ci NULL
        ');
    }
}
