<?php

namespace AhmadChebbo\LaravelHyperpay\Console\Commands;

use Illuminate\Console\Command;
use AhmadChebbo\LaravelHyperpay\Services\HyperPayService;
use AhmadChebbo\LaravelHyperpay\Services\HyperPayResultCodeService;

class HyperPayStatusCommand extends Command
{
    protected $signature = 'hyperpay:status
                            {payment-id : The payment ID to check}
                            {--brand=VISA : Payment brand (VISA, MASTER, MADA)}
                            {--detailed : Show detailed payment information}';

    protected $description = 'Check payment status from HyperPay';

    protected HyperPayService $hyperPayService;
    protected HyperPayResultCodeService $resultCodeService;

    public function __construct(
        HyperPayService $hyperPayService,
        HyperPayResultCodeService $resultCodeService
    ) {
        parent::__construct();
        $this->hyperPayService = $hyperPayService;
        $this->resultCodeService = $resultCodeService;
    }

    public function handle(): int
    {
        $paymentId = $this->argument('payment-id');
        $brand = strtoupper($this->option('brand'));
        $detailed = $this->option('detailed');

        $this->info("Checking payment status for ID: {$paymentId}");
        $this->line("Brand: {$brand}");
        $this->newLine();

        try {
            $response = $this->hyperPayService->getPaymentStatus($paymentId, $brand);

            $this->displayPaymentStatus($response, $detailed);

            return self::SUCCESS;

        } catch (\Exception $e) {
            $this->error("Error checking payment status: {$e->getMessage()}");
            return self::FAILURE;
        }
    }

    protected function displayPaymentStatus($response, bool $detailed): void
    {
        // Basic status information
        $this->info('Payment Status:');
        $this->table(
            ['Field', 'Value'],
            [
                ['Payment ID', $response->getId()],
                ['Result Code', $response->getResultCode()],
                ['Result Description', $response->getResultDescription()],
                ['Status', $this->getStatusText($response)],
                ['Amount', $response->getAmount() . ' ' . $response->getCurrency()],
                ['Payment Brand', $response->getPaymentBrand()],
                ['Merchant Transaction ID', $response->getMerchantTransactionId()],
                ['Timestamp', $response->getTimestamp()],
            ]
        );

        if ($detailed) {
            $this->displayDetailedInformation($response);
        }

        // Result code analysis
        $this->displayResultCodeAnalysis($response);
    }

    protected function getStatusText($response): string
    {
        if ($response->isSuccessful()) {
            return '<fg=green>SUCCESSFUL</>';
        } elseif ($response->isPending()) {
            return '<fg=yellow>PENDING</>';
        } elseif ($response->isRejected()) {
            return '<fg=red>REJECTED</>';
        } elseif ($response->needsReview()) {
            return '<fg=blue>NEEDS REVIEW</>';
        } else {
            return '<fg=red>FAILED</>';
        }
    }

    protected function displayDetailedInformation($response): void
    {
        $this->newLine();
        $this->info('Detailed Information:');
        $this->table(
            ['Field', 'Value'],
            [
                ['Category', $this->resultCodeService->getCategory($response->getResultCode())],
                ['Suggested Action', $this->resultCodeService->getSuggestedAction($response->getResultCode())],
                ['Can Retry', $this->resultCodeService->canRetry($response->getResultCode()) ? 'Yes' : 'No'],
                ['Description', $this->resultCodeService->getDescription($response->getResultCode())],
            ]
        );
    }

    protected function displayResultCodeAnalysis($response): void
    {
        $this->newLine();
        $this->info('Result Code Analysis:');

        $resultCode = $response->getResultCode();
        $description = $this->resultCodeService->getDescription($resultCode);
        $category = $this->resultCodeService->getCategory($resultCode);
        $suggestedAction = $this->resultCodeService->getSuggestedAction($resultCode);
        $canRetry = $this->resultCodeService->canRetry($resultCode);

        $this->line("Result Code: <fg=cyan>{$resultCode}</>");
        $this->line("Description: {$description}");
        $this->line("Category: <fg=blue>{$category}</>");
        $this->line("Suggested Action: {$suggestedAction}");
        $this->line("Can Retry: " . ($canRetry ? '<fg=green>Yes</>' : '<fg=red>No</>'));

        if (!$response->isSuccessful()) {
            $this->newLine();
            $this->warn('Payment was not successful. Please review the result code and take appropriate action.');
        }
    }
}
