<?php declare(strict_types=1);

namespace Sidworks\ProductLabels\ScheduledTask;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: LabelsTask::class)]
class LabelsTaskHandler extends ScheduledTaskHandler
{
    public function __construct(
        EntityRepository $scheduledTaskRepository,
        private readonly EntityRepository $productLabelsRepository,
        ?LoggerInterface $exceptionLogger = null
    ) {
        parent::__construct($scheduledTaskRepository, $exceptionLogger);
    }

    public function run(): void
    {
        $this->handleLabelsStatus();
    }

    private function handleLabelsStatus(): void
    {
        $context = Context::createDefaultContext();
        $criteria = new Criteria();
        $now = new \DateTimeImmutable();

        $productLabels = $this->productLabelsRepository
            ->search($criteria, $context)
            ->getEntities();

        if (!$productLabels->count()) {
            return;
        }

        foreach ($productLabels as $productLabel) {
            $fromDateTime = $productLabel->getFromDateTime();
            $toDateTime = $productLabel->getToDateTime();

            $newActive = match (true) {
                $fromDateTime && $fromDateTime > $now => false,
                $toDateTime && $toDateTime < $now => false,
                $fromDateTime && $fromDateTime <= $now && (!$toDateTime || $toDateTime >= $now) => true,
                default => $productLabel->getActive()
            };

            if ($productLabel->getActive() !== $newActive) {
                $this->productLabelsRepository->update([
                    [
                        'id' => $productLabel->getId(),
                        'active' => $newActive,
                    ],
                ], $context);
            }
        }
    }
}
