<?php declare(strict_types=1);

namespace Sidworks\ProductLabels\ScheduledTask;

use Psr\Log\LoggerInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepository;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;
use Shopware\Core\Framework\MessageQueue\ScheduledTask\ScheduledTaskHandler;
use Sidworks\ProductLabels\Service\LabelStreamService;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(handles: LabelsTask::class)]
class LabelsTaskHandler extends ScheduledTaskHandler
{
    public function __construct(
        EntityRepository $scheduledTaskRepository,
        private readonly EntityRepository $productLabelsRepository,
        private readonly LabelStreamService $labelStreamService,
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
        $criteria->addFilter(new EqualsFilter('fromToActive', 1));

        $productLabels = $this->productLabelsRepository
            ->search($criteria, $context)
            ->getEntities();

        if (!$productLabels->count()) {
            return;
        }

        foreach ($productLabels as $productLabel) {
            $newActive = $this->labelStreamService->shouldShowProductLabel($productLabel);

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
