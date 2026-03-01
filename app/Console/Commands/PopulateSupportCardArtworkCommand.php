<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\SupportCardDefinition;
use Illuminate\Console\Command;

/**
 * Populate Support Card Artwork Command
 *
 * Populates artwork_url for all support cards using the GameTora CDN pattern:
 * https://gametora.com/images/umamusume/supports/tex_support_card_{id}.png
 */
class PopulateSupportCardArtworkCommand extends Command
{
    /**
     * The GameTora CDN base URL for support card artwork.
     */
    protected const GAMETORA_CDN_BASE = 'https://gametora.com/images/umamusume/supports/tex_support_card_';

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'support-cards:populate-artwork
                            {--dry-run : Preview changes without writing to the database}
                            {--force : Overwrite artwork_url even for cards that already have one}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate artwork_url for all support cards using the GameTora CDN';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $isDryRun = (bool) $this->option('dry-run');
        $isForce = (bool) $this->option('force');

        $this->info('Support Card Artwork Population');
        $this->newLine();

        if ($isDryRun) {
            $this->warn('DRY RUN — no database changes will be made.');
            $this->newLine();
        }

        $query = SupportCardDefinition::query()
            ->whereNotNull('external_source_id');

        if (! $isForce) {
            $query->whereNull('artwork_url');
        }

        $total = $query->count();

        if ($total === 0) {
            $this->info($isForce
                ? 'No cards with an external_source_id found.'
                : 'All cards already have artwork_url set. Use --force to overwrite.'
            );

            return Command::SUCCESS;
        }

        $this->info(sprintf(
            'Found %d card(s) to update%s.',
            $total,
            $isForce ? ' (--force mode: including already-set artwork)' : ' (missing artwork_url)'
        ));
        $this->newLine();

        $progressBar = $this->output->createProgressBar($total);
        $progressBar->setFormat(' %current%/%max% [%bar%] %percent:3s%% — %message%');
        $progressBar->setMessage('Starting…');
        $progressBar->start();

        $updated = 0;
        $skipped = 0;

        $query->chunkById(100, function ($cards) use (&$updated, &$skipped, $isDryRun, $progressBar): void {
            foreach ($cards as $card) {
                $sourceId = $card->external_source_id;

                if (! is_numeric($sourceId)) {
                    $skipped++;
                    $progressBar->advance();
                    $progressBar->setMessage("Skipped: {$card->name} (non-numeric ID)");

                    continue;
                }

                $artworkUrl = self::GAMETORA_CDN_BASE.$sourceId.'.png';

                $progressBar->setMessage(mb_substr($card->name, 0, 50));

                if (! $isDryRun) {
                    /** @var \App\Models\SupportCardDefinition $card */
                    $card->artwork_url = $artworkUrl;
                    $card->save();
                }

                $updated++;
                $progressBar->advance();
            }
        });

        $progressBar->finish();
        $this->newLine(2);

        $this->info('Artwork Population Complete!');
        $this->table(
            ['Metric', 'Count'],
            [
                ['Updated', $updated],
                ['Skipped (non-numeric ID)', $skipped],
            ]
        );

        if ($isDryRun) {
            $this->newLine();
            $this->warn('DRY RUN — re-run without --dry-run to apply changes.');
        }

        return Command::SUCCESS;
    }
}
