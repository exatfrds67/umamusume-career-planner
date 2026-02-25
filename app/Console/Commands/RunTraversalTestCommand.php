<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

/**
 * Run Browser Traversal Test Command
 *
 * Provides a convenient Artisan command to run the comprehensive
 * browser traversal test and view the generated report.
 */
class RunTraversalTestCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:traversal
                            {--scope=all : Scope of testing (all, public, auth, admin)}
                            {--headless=true : Run in headless mode}
                            {--open-report : Open HTML report in browser after completion}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Run comprehensive browser traversal test across all application routes';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('🚀 Starting Comprehensive Browser Traversal Test...');
        $this->newLine();

        $scope = $this->option('scope');
        $headless = $this->option('headless');

        // Build test command
        $command = $this->buildTestCommand($scope, $headless);

        $this->line("Running: <comment>{$command}</comment>");
        $this->newLine();

        // Run the test
        $process = Process::fromShellCommandline($command, base_path(), [
            'HEADLESS' => $headless,
        ]);

        $process->setTimeout(600); // 10 minutes timeout

        $exitCode = $process->run(function ($type, $buffer) {
            echo $buffer;
        });

        $this->newLine();

        if ($exitCode === 0) {
            $this->info('✅ Traversal test completed successfully!');
            $this->newLine();

            // Show report location
            $reportPath = storage_path('app/test-reports/traversal-latest.html');

            if (file_exists($reportPath)) {
                $this->line("📊 <info>Report generated:</info> {$reportPath}");

                if ($this->option('open-report')) {
                    $this->openReportInBrowser($reportPath);
                } else {
                    $this->line('💡 Tip: Use <comment>--open-report</comment> to automatically open the report in your browser');
                }
            }

            return self::SUCCESS;
        } else {
            $this->error('❌ Traversal test failed!');

            return self::FAILURE;
        }
    }

    /**
     * Build the test command based on options.
     */
    private function buildTestCommand(string $scope, string $headless): string
    {
        $baseCommand = 'php artisan test tests/Browser/ComprehensiveTraversalTest.php --compact';

        // Add scope filter if not 'all'
        if ($scope !== 'all') {
            $baseCommand .= " --group={$scope}";
        }

        return $baseCommand;
    }

    /**
     * Open report in default browser.
     */
    private function openReportInBrowser(string $reportPath): void
    {
        $this->line('🌐 Opening report in browser...');

        // Detect OS and use appropriate command
        if (PHP_OS_FAMILY === 'Windows') {
            exec("start \"\" \"{$reportPath}\"");
        } elseif (PHP_OS_FAMILY === 'Darwin') {
            exec("open \"{$reportPath}\"");
        } else {
            exec("xdg-open \"{$reportPath}\"");
        }
    }
}
