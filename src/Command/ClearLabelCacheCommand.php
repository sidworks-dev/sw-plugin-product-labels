<?php declare(strict_types=1);

namespace Sidworks\ProductLabels\Command;

use Sidworks\ProductLabels\Service\LabelStreamService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'sidworks:labels:cache:clear',
    description: 'Clear the product labels cache'
)]
class ClearLabelCacheCommand extends Command
{
    public function __construct(
        private readonly LabelStreamService $labelStreamService
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption(
            'sales-channel-id',
            's',
            InputOption::VALUE_OPTIONAL,
            'Clear cache for specific sales channel ID only'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $salesChannelId = $input->getOption('sales-channel-id');

        $io->info('Clearing product labels cache...');

        try {
            $this->labelStreamService->invalidateCache($salesChannelId);

            if ($salesChannelId) {
                $io->success(sprintf('Product labels cache cleared for sales channel: %s', $salesChannelId));
            } else {
                $io->success('Product labels cache cleared successfully for all sales channels');
            }

            return Command::SUCCESS;
        } catch (\Exception $e) {
            $io->error('Failed to clear cache: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
