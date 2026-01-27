<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\ExternalDataService;
use Illuminate\Console\Command;

/**
 * Sync Support Cards Command
 *
 * Fetches and syncs support card data from umapyoi.net API to the local database.
 * Supports filtering by rarity and limiting the number of cards synced.
 */
class SyncSupportCardsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'support-cards:sync
                            {--force : Force refresh, ignore cache}
                            {--rarity= : Filter by rarity (R, SR, SSR, or all)}
                            {--limit= : Limit number of cards to sync}
                            {--dry-run : Preview without making changes}
                            {--stats : Show statistics only}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync support cards from umapyoi.net API';

    /**
     * Execute the console command.
     */
    public function handle(ExternalDataService $externalDataService): int
    {
        // Show stats only mode
        if ($this->option('stats')) {
            return $this->showStats($externalDataService);
        }

        $this->info('Syncing support cards from umapyoi.net...');
        $this->newLine();

        // Check API availability
        if (! $externalDataService->isApiAvailable()) {
            $this->error('umapyoi.net API is not available. Please try again later.');

            return Command::FAILURE;
        }

        // Clear cache if force option is set
        if ($this->option('force')) {
            $this->info('Clearing cache...');
            $externalDataService->clearCache();
        }

        // Get options
        $rarity = $this->option('rarity');
        $limit = $this->option('limit');
        $dryRun = $this->option('dry-run');

        // Validate rarity option
        if ($rarity !== null && ! \in_array(strtoupper((string) $rarity), ['R', 'SR', 'SSR', 'ALL'], true)) {
            $this->error('Invalid rarity. Use R, SR, SSR, or all.');

            return Command::FAILURE;
        }

        if ($rarity !== null && strtoupper((string) $rarity) === 'ALL') {
            $rarity = null;
        }

        // Dry run mode
        if ($dryRun) {
            return $this->dryRun($externalDataService, $rarity !== null ? (string) $rarity : null);
        }

        // Fetch cards first to get total count
        $this->info('Fetching card data from API...');
        $allCards = $externalDataService->fetchAllSupportCards();

        if (empty($allCards)) {
            $this->error('Failed to fetch cards from API.');

            return Command::FAILURE;
        }

        $this->info('Found '.\count($allCards).' cards in API.');
        $this->newLine();

        // Create progress bar
        $progressBar = $this->output->createProgressBar(\count($allCards));
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s%');

        // Sync cards with progress callback
        $result = $externalDataService->syncAllSupportCards(
            $rarity !== null ? (string) $rarity : null,
            $limit !== null ? (int) $limit : null,
            function ($current, $total) use ($progressBar) {
                $progressBar->setProgress($current);
            }
        );

        $progressBar->finish();
        $this->newLine(2);

        // Display results
        if ($result['success']) {
            $this->displaySuccessReport($result);

            return Command::SUCCESS;
        }

        $this->error('Sync failed.');
        $this->displayErrors($result['errors']);

        return Command::FAILURE;
    }

    /**
     * Show statistics only
     */
    protected function showStats(ExternalDataService $externalDataService): int
    {
        $this->info('Fetching support card statistics...');
        $this->newLine();

        $stats = $externalDataService->getSupportCardStats();

        $this->table(
            ['Metric', 'Count'],
            [
                ['Total Cards', $stats['total']],
                ['SSR Cards', $stats['by_rarity']['SSR']],
                ['SR Cards', $stats['by_rarity']['SR']],
                ['R Cards', $stats['by_rarity']['R']],
            ]
        );

        $this->newLine();
        $this->info('Cards by Type:');

        $this->table(
            ['Type', 'Count'],
            [
                ['Speed', $stats['by_type']['speed']],
                ['Stamina', $stats['by_type']['stamina']],
                ['Power', $stats['by_type']['power']],
                ['Guts', $stats['by_type']['guts']],
                ['Wit', $stats['by_type']['wit']],
                ['Friend', $stats['by_type']['friend']],
            ]
        );

        return Command::SUCCESS;
    }

    /**
     * Dry run mode - preview without making changes
     */
    protected function dryRun(ExternalDataService $externalDataService, ?string $rarity): int
    {
        $this->info('DRY RUN MODE - No changes will be made');
        $this->newLine();

        $cards = $externalDataService->fetchAllSupportCards();

        if (empty($cards)) {
            $this->error('Failed to fetch cards from API.');

            return Command::FAILURE;
        }

        // Filter by rarity if specified
        if ($rarity !== null) {
            $cards = array_filter($cards, function ($card) use ($rarity, $externalDataService) {
                $cardId = isset($card['id']) && is_numeric($card['id']) ? (int) $card['id'] : 0;
                $cardRarity = $externalDataService->determineRarity($cardId);

                return strtoupper($cardRarity) === strtoupper($rarity);
            });
        }

        $this->info('Would sync '.\count($cards).' cards:');
        $this->newLine();

        // Show sample of cards
        $sample = \array_slice($cards, 0, 10);
        $tableData = [];

        foreach ($sample as $card) {
            $id = $card['id'] ?? 'N/A';
            $charaId = isset($card['chara_id']) && is_numeric($card['chara_id']) ? (int) $card['chara_id'] : 'N/A';
            $title = isset($card['title_en']) && is_string($card['title_en']) ? $card['title_en'] : '(No English title)';
            $cardId = isset($card['id']) && is_numeric($card['id']) ? (int) $card['id'] : 0;
            $cardRarity = $externalDataService->determineRarity($cardId);

            $tableData[] = [$id, $charaId, $cardRarity, mb_substr($title, 0, 40)];
        }

        $this->table(['ID', 'Chara ID', 'Rarity', 'Title'], $tableData);

        if (\count($cards) > 10) {
            $this->info('... and '.(\count($cards) - 10).' more cards');
        }

        return Command::SUCCESS;
    }

    /**
     * Display success report
     *
     * @param  array{success: bool, synced_count: int, skipped_count: int, errors: array<string>, source: string}  $result
     */
    protected function displaySuccessReport(array $result): void
    {
        $this->info('✓ Sync completed successfully!');
        $this->newLine();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Cards Synced', $result['synced_count']],
                ['Cards Skipped', $result['skipped_count']],
                ['Errors', \count($result['errors'])],
                ['Source', $result['source']],
            ]
        );

        if (! empty($result['errors'])) {
            $this->newLine();
            $this->warn('Errors encountered:');
            $this->displayErrors(\array_slice($result['errors'], 0, 5));

            if (\count($result['errors']) > 5) {
                $this->warn('... and '.(\count($result['errors']) - 5).' more errors');
            }
        }
    }

    /**
     * Display errors
     *
     * @param  array<string>  $errors
     */
    protected function displayErrors(array $errors): void
    {
        foreach ($errors as $error) {
            $this->error("  - {$error}");
        }
    }
}
