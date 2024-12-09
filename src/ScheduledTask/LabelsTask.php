<?php declare(strict_types=1);

namespace Sidworks\ProductLabels\ScheduledTask;

use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTask;

class LabelsTask extends ScheduledTask
{
    public static function getTaskName(): string
    {
        return 'sidworks.product_labels.handle_labels_task';
    }

    public static function getDefaultInterval(): int
    {
        return self::MINUTELY;
    }
}
