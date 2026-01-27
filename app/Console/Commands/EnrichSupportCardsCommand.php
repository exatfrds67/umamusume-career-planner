<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Services\SupportCardEnrichmentService;
use Illuminate\Console\Command;

/**
 * Enrich Support Cards Command
 *
 * Enriches support card data from GameTora and applies meta tier rankings.
 */
class EnrichSupportCardsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'support-cards:enrich
                            {--meta-only : Only apply meta tier data without scraping}
                            {--force : Force re-enrichment of all cards}
                            {--limit= : Limit number of cards to enrich}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Enrich support cards with detailed data from GameTora';

    /**
     * Execute the console command.
     */
    public function handle(SupportCardEnrichmentService $enrichmentService): int
    {
        $this->info('Support Card Enrichment');
        $this->newLine();

        // Apply meta data first
        $this->info('Applying meta tier rankings...');
        $metaResult = $enrichmentService->applyMetaData();
        $this->info("  - Updated: {$metaResult['updated']} cards");
        $this->info("  - Skipped: {$metaResult['skipped']} cards");
        $this->newLine();

        if ($this->option('meta-only')) {
            $this->info('Meta-only mode - skipping GameTora enrichment.');

            return Command::SUCCESS;
        }

        // Enrich from GameTora
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

        // Display results
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
