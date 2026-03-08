<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\SupportCardBulkEnrichmentService;
use App\Services\SupportCardEnrichmentService;
use Illuminate\Console\Command;

/**
 * Enrich Support Cards Command
 *
 * Enriches support card data via:
 * - Bulk API enrichment (fetches correct types + generates game-accurate data for all cards)
 * - GameTora scraping (detailed effects, skills, events)
 * - Static meta tier rankings
 */
class EnrichSupportCardsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'support-cards:enrich
                            {--bulk : Bulk enrich ALL cards from umapyoi API + generation}
                            {--meta-only : Only apply meta tier data without scraping}
                            {--force : Force re-enrichment of all cards}
                            {--limit= : Limit number of cards to enrich}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enrich support cards with detailed data (bulk API, GameTora, meta tiers)';

    /**
     * Execute the console command.
     */
    public function handle(
        SupportCardEnrichmentService $enrichmentService,
        SupportCardBulkEnrichmentService $bulkService
    ): int {
        $this->info('Support Card Enrichment');
        $this->newLine();

        if ($this->option('bulk')) {
            return $this->handleBulkEnrichment($bulkService, $enrichmentService);
        }

        return $this->handleStandardEnrichment($enrichmentService);
    }

    /**
     * Handle bulk enrichment from API + generation
     */
    protected function handleBulkEnrichment(
        SupportCardBulkEnrichmentService $bulkService,
        SupportCardEnrichmentService $enrichmentService
    ): int {
        $this->info('=== Bulk Enrichment Mode ===');
        $this->info('This will:');
        $this->info('  1. Fetch correct card types from umapyoi.net API');
        $this->info('  2. Generate game-accurate enrichment for all sparse cards');
        $this->info('  3. Preserve existing manually-curated card data');
        $this->warn('Note: This calls the API for each card (~100ms delay). May take 1-2 minutes.');
        $this->newLine();

        $progressBar = $this->output->createProgressBar(100);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');
        $progressBar->setMessage('Starting...');

        $result = $bulkService->enrichAllCards(function ($current, $total, $cardName) use ($progressBar) {
            $progressBar->setMaxSteps($total);
            $progressBar->setProgress($current);
            $progressBar->setMessage(mb_substr($cardName, 0, 40));
        });

        $progressBar->finish();
        $this->newLine(2);

        $this->info('Bulk Enrichment Complete!');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Card Types Fixed', $result['fixed_types']],
                ['Cards Enriched', $result['enriched']],
                ['Skipped (manually curated)', $result['skipped']],
                ['Errors', $result['errors']],
                ['API Errors', $result['api_errors']],
            ]
        );

        $this->newLine();
        $this->info('Applying meta tier rankings...');
        $metaResult = $enrichmentService->applyMetaData();
        $this->info("  - Updated: {$metaResult['updated']} cards");
        $this->info("  - Skipped: {$metaResult['skipped']} cards");

        return Command::SUCCESS;
    }

    /**
     * Handle standard enrichment (GameTora + meta)
     */
    protected function handleStandardEnrichment(SupportCardEnrichmentService $enrichmentService): int
    {
        $this->info('Applying meta tier rankings...');
        $metaResult = $enrichmentService->applyMetaData();
        $this->info("  - Updated: {$metaResult['updated']} cards");
        $this->info("  - Skipped: {$metaResult['skipped']} cards");
        $this->newLine();

        if ($this->option('meta-only')) {
            $this->info('Meta-only mode - skipping GameTora enrichment.');

            return Command::SUCCESS;
        }

        $this->info('Enriching cards from GameTora...');
        $this->warn('Note: This may take several minutes due to rate limiting.');
        $this->newLine();

        $progressBar = $this->output->createProgressBar(100);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% - %message%');
        $progressBar->setMessage('Starting...');

        $result = $enrichmentService->enrichAllCards(function ($current, $total, $cardName) use ($progressBar) {
            $progressBar->setMaxSteps($total);
            $progressBar->setProgress($current);
            $progressBar->setMessage(mb_substr($cardName, 0, 40));
        });

        $progressBar->finish();
        $this->newLine(2);

        $this->info('Enrichment Complete!');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Enriched', $result['enriched']],
                ['Failed', $result['failed']],
                ['Skipped (already enriched)', $result['skipped']],
            ]
        );

        return Command::SUCCESS;
    }
}
