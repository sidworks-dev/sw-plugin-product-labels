<?php declare(strict_types=1);

namespace Sidworks\ProductLabels\Migration;

use Doctrine\DBAL\Connection;
use Shopware\Core\Framework\Migration\MigrationStep;

/**
 * @internal
 */
class Migration1783684686AddLabelLinkNewTab extends MigrationStep
{
    public function getCreationTimestamp(): int
    {
        return 1783684686;
    }

    public function update(Connection $connection): void
    {
        if ($this->columnExists($connection, 'sidworks_product_labels_translation', 'link_new_tab')) {
            return;
        }

        $connection->executeStatement('
            ALTER TABLE `sidworks_product_labels_translation`
            ADD COLUMN `link_new_tab` TINYINT(1) NULL DEFAULT 0 AFTER `link`
        ');
    }
}
