<?php

namespace AhmadChebbo\LaravelHyperpay\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class HyperPayInstallCommand extends Command
{
    protected $signature = 'hyperpay:install
                            {--force : Overwrite existing files}
                            {--env : Setup environment variables}';

    protected $description = 'Install HyperPay package';

    public function handle(): int
    {
        $this->info('Installing HyperPay package...');

        // Publish configuration
        $this->publishConfig();

        // Publish migrations
        $this->publishMigrations();

        // Publish views
        $this->publishViews();

        // Setup environment variables
        if ($this->option('env')) {
            $this->setupEnvironment();
        }

        $this->info('HyperPay package installed successfully!');

        $this->comment('Next steps:');
        $this->line('1. Configure your HyperPay credentials in .env file');
        $this->line('2. Run: php artisan migrate');
        $this->line('3. Review the configuration in config/hyperpay.php');

        return self::SUCCESS;
    }

    protected function publishConfig(): void
    {
        $this->info('Publishing configuration...');

        $this->callSilent('vendor:publish', [
            '--provider' => 'AhmadChebbo\LaravelHyperpay\HyperPayServiceProvider',
            '--tag' => 'hyperpay-config',
            '--force' => $this->option('force'),
        ]);
    }

    protected function publishMigrations(): void
    {
        $this->info('Publishing migrations...');

        $this->callSilent('vendor:publish', [
            '--provider' => 'AhmadChebbo\LaravelHyperpay\HyperPayServiceProvider',
            '--tag' => 'hyperpay-migrations',
            '--force' => $this->option('force'),
        ]);
    }

    protected function publishViews(): void
    {
        $this->info('Publishing views...');

        $this->callSilent('vendor:publish', [
            '--provider' => 'AhmadChebbo\LaravelHyperpay\HyperPayServiceProvider',
            '--tag' => 'hyperpay-views',
            '--force' => $this->option('force'),
        ]);
    }

    protected function setupEnvironment(): void
    {
        $this->info('Setting up environment variables...');

        $envFile = base_path('.env');
        $envContent = File::exists($envFile) ? File::get($envFile) : '';

        $envVars = [
            'HYPERPAY_ENVIRONMENT' => 'test',
            'HYPERPAY_TEST_URL' => 'https://eu-test.oppwa.com',
            'HYPERPAY_TEST_TOKEN' => '',
            'HYPERPAY_TEST_VISA_ENTITY_ID' => '',
            'HYPERPAY_TEST_MASTER_ENTITY_ID' => '',
            'HYPERPAY_TEST_MADA_ENTITY_ID' => '',
            'HYPERPAY_LIVE_URL' => 'https://oppwa.com',
            'HYPERPAY_LIVE_TOKEN' => '',
            'HYPERPAY_LIVE_VISA_ENTITY_ID' => '',
            'HYPERPAY_LIVE_MASTER_ENTITY_ID' => '',
            'HYPERPAY_LIVE_MADA_ENTITY_ID' => '',
            'HYPERPAY_CURRENCY' => 'SAR',
            'HYPERPAY_WEBHOOK_ENABLED' => 'true',
            'HYPERPAY_LOGGING_ENABLED' => 'true',
        ];

        $newEnvContent = $envContent;

        foreach ($envVars as $key => $defaultValue) {
            if (str_contains($envContent, $key)) {
                $this->info("{$key} already exists in .env file");

                continue;
            }

            $newEnvContent .= "\n{$key}={$defaultValue}";
        }

        if ($newEnvContent !== $envContent) {
            File::put($envFile, $newEnvContent);
            $this->info('Environment variables updated successfully');
        }
    }
}
