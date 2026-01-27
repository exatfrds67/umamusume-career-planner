<?php

namespace App\Console\Commands;

use App\Services\ExternalAPI\UmapyoiApiClient;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class TestOfflineMode extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'external:test-offline {--restore : Restore normal API URL}';

    /**
     * The console command description.
     */
    protected $description = 'Test offline mode by temporarily breaking the API URL';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if ($this->option('restore')) {
            // Restore normal API URL
            Config::set('services.umapyoi.url', 'https://api.umapyoi.net');
            $this->info('✅ API URL restored to: https://api.umapyoi.net');

            return Command::SUCCESS;
        }

        // Break the API URL to test offline mode
        Config::set('services.umapyoi.url', 'https://broken-api-url-for-testing.invalid');
        $this->info('🔧 API URL temporarily set to broken URL for testing');

        // Clear cache to force fresh API calls
        Cache::flush();
        $this->info('🗑️ Cache cleared');

        // Test the client
        $client = app(UmapyoiApiClient::class);

        $this->info('🧪 Testing characters endpoint...');
        $result = $client->getCharacters();

        $this->info('Result:');
        $this->info('- Success: '.($result['success'] ? 'YES' : 'NO'));
        $this->info('- Source: '.$result['source']);
        $this->info('- Count: '.count($result['data']));

        if ($result['success'] && count($result['data']) > 0) {
            $firstName = 'Unknown';
            if (isset($result['data'][0]) && is_array($result['data'][0]) && isset($result['data'][0]['name']) && is_string($result['data'][0]['name'])) {
                $firstName = $result['data'][0]['name'];
            }
            $this->info('- First character: '.$firstName);

            if ($result['source'] === 'database_cache') {
                $this->info('✅ Database fallback is working!');
            } else {
                $this->warn('⚠️ Expected database fallback but got: '.$result['source']);
            }
        } else {
            $this->error('❌ No data available (API failed and no database fallback)');
        }

        $this->newLine();
        $this->info('💡 Run with --restore to restore normal API URL');

        return Command::SUCCESS;
    }
}
