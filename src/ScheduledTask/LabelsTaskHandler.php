<?php declare(strict_types=1);

namespace Sidworks\ProductLabels\ScheduledTask;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: LabelsTask::class)]
class LabelsTaskHandler extends ScheduledTaskHandler
{
    public function run(): void
    {

    }
}
