<?php

namespace App\Console\Commands;

use App\Services\ExternalAPI\UmapyoiApiClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class CacheExternalData extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'external:cache {--force : Force refresh even if cache exists}';

    /**
     * The console command description.
     */
    protected $description = 'Cache external API data for offline access';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🔄 Caching external API data for offline access...');
        $this->newLine();

        $client = app(UmapyoiApiClient::class);
        $endpoints = [
            'characters' => 'Characters',
            'support_cards' => 'Support Cards',
            'news' => 'News',
        ];

        $successCount = 0;
        $totalCount = count($endpoints);

        // Clear cache if force option is used
        if ($this->option('force')) {
            $this->info('🗑️ Force refresh enabled - clearing existing cache...');
            Cache::flush();
        }

        foreach ($endpoints as $method => $label) {
            try {
                $this->info("📡 Fetching {$label}...");

                // Call the appropriate method
                $methodName = match ($method) {
                    'characters' => 'getCharacters',
                    'support_cards' => 'getSupportCards',
                    'news' => 'getNews',
                };

                if (method_exists($client, $methodName)) {
                    if ($method === 'news') {
                        /** @var array{success: bool, data: array<mixed>, source: string} */
                        $data = $client->getNews(10);
                    } elseif ($method === 'characters') {
                        $forceRefresh = $this->option('force');
                        $forceRefreshBool = (bool) $forceRefresh;
                        /** @var array{success: bool, data: array<mixed>, source: string} */
                        $data = $client->getCharacters($forceRefreshBool);
                    } else { // support_cards
                        $forceRefresh = $this->option('force');
                        $forceRefreshBool = (bool) $forceRefresh;
                        /** @var array{success: bool, data: array<mixed>, source: string} */
                        $data = $client->getSupportCards($forceRefreshBool);
                    }

                    if ($data['success']) {
                        $count = count($data['data']);
                        $source = $data['source'];

                        if ($count > 0) {
                            $sourceIcon = match ($source) {
                                'api' => '🌐',
                                'cache' => '💾',
                                'database_cache' => '🗄️',
                                default => '📊'
                            };

                            $this->info("✅ {$label}: {$count} items cached {$sourceIcon} ({$source})");
                            $successCount++;
                        } else {
                            $this->warn("⚠️ {$label}: No data received");
                        }
                    } else {
                        $this->error("❌ {$label}: ".($data['error'] ?? 'Unknown error'));
                    }
                } else {
                    $this->error("❌ {$label}: Method {$methodName} not found");
                }
            } catch (\Exception $e) {
                $this->error("❌ {$label}: {$e->getMessage()}");
                Log::error("Failed to cache {$method}", [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }

        $this->newLine();

        if ($successCount === $totalCount) {
            $this->info("🎉 All external data cached successfully ({$successCount}/{$totalCount})");
            $this->info('✅ Users can now access data offline!');

            return Command::SUCCESS;
        } elseif ($successCount > 0) {
            $this->warn("⚠️ Partial success: {$successCount}/{$totalCount} endpoints cached");
            $this->info('✅ Some offline data is available');

            return Command::SUCCESS;
        } else {
            $this->error('❌ Failed to cache any external data');
            $this->error('⚠️ No offline data available');

            return Command::FAILURE;
        }
    }
}
