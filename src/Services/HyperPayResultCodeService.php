<?php

namespace Ahmad-Chebbo\LaravelHyperpay\Services;

class HyperPayResultCodeService
{
    /**
     * Regular expression patterns for different result code groups
     */
    const PATTERNS = [
        'successful' => '/^(000\.000\.|000\.100\.1|000\.[36]|000\.400\.[1][12]0)/',
        'successful_review' => '/^(000\.400\.0[^3]|000\.400\.100)/',
        'pending' => '/^(000\.200)/',
        'pending_async' => '/^(800\.400\.5|100\.400\.500)/',
        'threeds_intercard' => '/^(000\.400\.[1][0-9][1-9]|000\.400\.2)/',
        'bank_declined' => '/^(800\.[17]00|800\.800\.[123])/',
        'communication_error' => '/^(900\.[1234]00|000\.400\.030)/',
        'system_error' => '/^(800\.[56]|999\.|600\.1|800\.800\.[84])/',
        'async_workflow_error' => '/^(100\.39[765])/',
        'soft_decline' => '/^(300\.100\.100)/',
        'external_risk' => '/^(100\.400\.[0-3]|100\.380\.100|100\.380\.11|100\.380\.4|100\.380\.5)/',
        'address_validation' => '/^(800\.400\.1)/',
        'threeds_rejection' => '/^(800\.400\.2|100\.390)/',
        'blacklist' => '/^(800\.[32])/',
        'risk_validation' => '/^(800\.1[123456]0)/',
        'configuration' => '/^(600\.[23]|500\.[12]|800\.121)/',
        'registration' => '/^(100\.[13]50)/',
        'job_validation' => '/^(100\.250|100\.360)/',
        'reference_validation' => '/^(700\.[1345][05]0)/',
        'format_validation' => '/^(200\.[123]|100\.[53][07]|800\.900|100\.[69]00\.500)/',
        'address_validation_detailed' => '/^(100\.800)/',
        'contact_validation' => '/^(100\.700|100\.900\.[123467890][00-99])/',
        'account_validation' => '/^(100\.100|100.2[01])/',
        'amount_validation' => '/^(100\.55)/',
        'risk_management' => '/^(100\.380\.[23]|100\.380\.101)/',
        'chargeback' => '/^(000\.100\.2)/',
    ];

    /**
     * Result code descriptions
     */
    const DESCRIPTIONS = [
        '000.000.000' => 'Transaction succeeded',
        '000.000.100' => 'Successful request',
        '000.100.105' => 'Chargeback Representment is successful',
        '000.100.106' => 'Chargeback Representment cancellation is successful',
        '000.100.110' => 'Request successfully processed in Merchant in Integrator Test Mode',
        '000.100.111' => 'Request successfully processed in Merchant in Validator Test Mode',
        '000.100.112' => 'Request successfully processed in Merchant in Connector Test Mode',
        '000.200.000' => 'Transaction pending',
        '000.200.100' => 'Successfully created checkout',
        '000.300.000' => 'Two-step transaction succeeded',
        '000.400.000' => 'Transaction succeeded (please review manually due to fraud suspicion)',
        '000.400.010' => 'Transaction succeeded (please review manually due to AVS return code)',
        '000.400.020' => 'Transaction succeeded (please review manually due to CVV return code)',
        '000.400.030' => 'Transaction partially failed (please reverse manually due to failed automatic reversal)',
        '000.400.040' => 'Transaction succeeded (please review manually due to amount mismatch)',
        '000.400.050' => 'Transaction succeeded (please review manually because transaction is pending)',
        '000.400.060' => 'Transaction succeeded (approved at merchants risk)',
        '000.400.070' => 'Transaction succeeded (waiting for external risk review)',
        '000.400.080' => 'Transaction succeeded (please review manually because the service was unavailable)',
        '000.400.090' => 'Transaction succeeded (please review manually due to external risk check)',
        '000.400.100' => 'Transaction succeeded, risk after payment rejected',
        '000.400.101' => 'Card not participating/authentication unavailable',
        '000.400.102' => 'User not enrolled',
        '000.400.110' => 'Authentication successful (frictionless flow)',
        '000.400.120' => 'Authentication successful (data only flow)',
        '800.100.100' => 'Transaction declined for unknown reason',
        '800.100.150' => 'Transaction declined (refund on gambling tx not allowed)',
        '800.100.151' => 'Transaction declined (invalid card)',
        '800.100.152' => 'Transaction declined by authorization system',
        '800.100.153' => 'Transaction declined (invalid CVV)',
        '800.100.154' => 'Transaction declined (transaction marked as invalid)',
        '800.100.155' => 'Transaction declined (amount exceeds credit)',
        '800.100.156' => 'Transaction declined (format error)',
        '800.100.157' => 'Transaction declined (wrong expiry date)',
        '800.100.158' => 'Transaction declined (suspecting manipulation)',
        '800.100.159' => 'Transaction declined (stolen card)',
        '800.100.160' => 'Transaction declined (card blocked)',
        '800.100.161' => 'Transaction declined (too many invalid tries)',
        '800.100.162' => 'Transaction declined (limit exceeded)',
        '800.100.163' => 'Transaction declined (maximum transaction frequency exceeded)',
        '800.100.164' => 'Transaction declined (merchants limit exceeded)',
        '800.100.165' => 'Transaction declined (card lost)',
        '800.100.166' => 'Transaction declined (Incorrect personal identification number)',
        '800.100.167' => 'Transaction declined (referencing transaction does not match)',
        '800.100.168' => 'Transaction declined (restricted card)',
        '800.100.169' => 'Transaction declined (card type is not processed by the authorization center)',
        '800.100.170' => 'Transaction declined (transaction not permitted)',
        '800.100.171' => 'Transaction declined (pick up card)',
        '800.100.172' => 'Transaction declined (account blocked)',
        '800.100.173' => 'Transaction declined (invalid currency, not processed by authorization center)',
        '800.100.174' => 'Transaction declined (invalid amount)',
        '800.100.175' => 'Transaction declined (invalid brand)',
        '800.100.176' => 'Transaction declined (account temporarily not available. Please try again later)',
        '800.100.177' => 'Transaction declined (amount field should not be empty)',
        '800.100.178' => 'Transaction declined (PIN entered incorrectly too often)',
        '800.100.179' => 'Transaction declined (exceeds withdrawal count limit)',
        '800.100.190' => 'Transaction declined (invalid configuration data)',
        '800.200.159' => 'Account or user is blacklisted (card stolen)',
        '800.200.160' => 'Account or user is blacklisted (card blocked)',
        '800.200.165' => 'Account or user is blacklisted (card lost)',
        '800.300.101' => 'Account or user is blacklisted',
        '800.300.102' => 'Country blacklisted',
        '800.300.200' => 'Email is blacklisted',
        '800.300.301' => 'IP blacklisted',
        '800.300.401' => 'BIN blacklisted',
        '800.400.100' => 'AVS Check Failed',
        '800.400.101' => 'Mismatch of AVS street value',
        '800.400.102' => 'Mismatch of AVS street number',
        '800.400.103' => 'Mismatch of AVS PO box value fatal',
        '800.400.104' => 'Mismatch of AVS zip code value fatal',
        '800.400.105' => 'Mismatch of AVS settings (AVSkip, AVIgnore, AVSRejectPolicy) value',
        '800.400.110' => 'AVS Check Failed. Amount has still been reserved on the customers card and will be released in a few business days. Please ensure the billing address is accurate before retrying the transaction.',
        '900.100.100' => 'Unexpected communication error with connector/acquirer',
        '900.100.200' => 'Error response from connector/acquirer',
        '900.100.300' => 'Timeout, uncertain result',
        '900.100.400' => 'Timeout at connectors/acquirer side',
        '900.100.500' => 'Timeout at connectors/acquirer side (try later)',
        '900.100.600' => 'Connector/acquirer currently down',
        '999.999.999' => 'UNDEFINED CONNECTOR/ACQUIRER ERROR',
    ];

    /**
     * Check if the result code indicates a successful transaction
     */
    public function isSuccessful(string $resultCode): bool
    {
        return preg_match(self::PATTERNS['successful'], $resultCode) === 1;
    }

    /**
     * Check if the result code indicates a successful transaction that needs manual review
     */
    public function isSuccessfulButNeedsReview(string $resultCode): bool
    {
        return preg_match(self::PATTERNS['successful_review'], $resultCode) === 1;
    }

    /**
     * Check if the result code indicates a pending transaction
     */
    public function isPending(string $resultCode): bool
    {
        return preg_match(self::PATTERNS['pending'], $resultCode) === 1 ||
               preg_match(self::PATTERNS['pending_async'], $resultCode) === 1;
    }

    /**
     * Check if the result code indicates a rejected transaction
     */
    public function isRejected(string $resultCode): bool
    {
        return ! $this->isSuccessful($resultCode) &&
               ! $this->isSuccessfulButNeedsReview($resultCode) &&
               ! $this->isPending($resultCode);
    }

    /**
     * Check if the result code indicates a bank declined transaction
     */
    public function isBankDeclined(string $resultCode): bool
    {
        return preg_match(self::PATTERNS['bank_declined'], $resultCode) === 1;
    }

    /**
     * Check if the result code indicates a communication error
     */
    public function isCommunicationError(string $resultCode): bool
    {
        return preg_match(self::PATTERNS['communication_error'], $resultCode) === 1;
    }

    /**
     * Check if the result code indicates a system error
     */
    public function isSystemError(string $resultCode): bool
    {
        return preg_match(self::PATTERNS['system_error'], $resultCode) === 1;
    }

    /**
     * Check if the result code indicates a 3D Secure related issue
     */
    public function is3DSecureIssue(string $resultCode): bool
    {
        return preg_match(self::PATTERNS['threeds_intercard'], $resultCode) === 1 ||
               preg_match(self::PATTERNS['threeds_rejection'], $resultCode) === 1;
    }

    /**
     * Check if the result code indicates a blacklist issue
     */
    public function isBlacklisted(string $resultCode): bool
    {
        return preg_match(self::PATTERNS['blacklist'], $resultCode) === 1;
    }

    /**
     * Check if the result code indicates a validation error
     */
    public function isValidationError(string $resultCode): bool
    {
        return preg_match(self::PATTERNS['format_validation'], $resultCode) === 1 ||
               preg_match(self::PATTERNS['address_validation'], $resultCode) === 1 ||
               preg_match(self::PATTERNS['contact_validation'], $resultCode) === 1 ||
               preg_match(self::PATTERNS['account_validation'], $resultCode) === 1 ||
               preg_match(self::PATTERNS['amount_validation'], $resultCode) === 1;
    }

    /**
     * Check if the result code indicates a configuration error
     */
    public function isConfigurationError(string $resultCode): bool
    {
        return preg_match(self::PATTERNS['configuration'], $resultCode) === 1;
    }

    /**
     * Check if the result code indicates a risk management issue
     */
    public function isRiskManagementIssue(string $resultCode): bool
    {
        return preg_match(self::PATTERNS['external_risk'], $resultCode) === 1 ||
               preg_match(self::PATTERNS['risk_validation'], $resultCode) === 1 ||
               preg_match(self::PATTERNS['risk_management'], $resultCode) === 1;
    }

    /**
     * Check if the result code indicates a chargeback
     */
    public function isChargeback(string $resultCode): bool
    {
        return preg_match(self::PATTERNS['chargeback'], $resultCode) === 1;
    }

    /**
     * Check if the result code indicates a soft decline (requires customer authentication)
     */
    public function isSoftDecline(string $resultCode): bool
    {
        return preg_match(self::PATTERNS['soft_decline'], $resultCode) === 1;
    }

    /**
     * Get the description for a result code
     */
    public function getDescription(string $resultCode): string
    {
        return self::DESCRIPTIONS[$resultCode] ?? 'Unknown result code';
    }

    /**
     * Get the category of a result code
     */
    public function getCategory(string $resultCode): string
    {
        if ($this->isSuccessful($resultCode)) {
            return 'successful';
        }

        if ($this->isSuccessfulButNeedsReview($resultCode)) {
            return 'successful_review_required';
        }

        if ($this->isPending($resultCode)) {
            return 'pending';
        }

        if ($this->isBankDeclined($resultCode)) {
            return 'bank_declined';
        }

        if ($this->is3DSecureIssue($resultCode)) {
            return '3d_secure_issue';
        }

        if ($this->isBlacklisted($resultCode)) {
            return 'blacklisted';
        }

        if ($this->isValidationError($resultCode)) {
            return 'validation_error';
        }

        if ($this->isConfigurationError($resultCode)) {
            return 'configuration_error';
        }

        if ($this->isRiskManagementIssue($resultCode)) {
            return 'risk_management_issue';
        }

        if ($this->isCommunicationError($resultCode)) {
            return 'communication_error';
        }

        if ($this->isSystemError($resultCode)) {
            return 'system_error';
        }

        if ($this->isChargeback($resultCode)) {
            return 'chargeback';
        }

        if ($this->isSoftDecline($resultCode)) {
            return 'soft_decline';
        }

        return 'unknown';
    }

    /**
     * Get detailed information about a result code
     */
    public function getResultInfo(string $resultCode): array
    {
        return [
            'code' => $resultCode,
            'description' => $this->getDescription($resultCode),
            'category' => $this->getCategory($resultCode),
            'is_successful' => $this->isSuccessful($resultCode),
            'needs_review' => $this->isSuccessfulButNeedsReview($resultCode),
            'is_pending' => $this->isPending($resultCode),
            'is_rejected' => $this->isRejected($resultCode),
            'is_bank_declined' => $this->isBankDeclined($resultCode),
            'is_3d_secure_issue' => $this->is3DSecureIssue($resultCode),
            'is_blacklisted' => $this->isBlacklisted($resultCode),
            'is_validation_error' => $this->isValidationError($resultCode),
            'is_configuration_error' => $this->isConfigurationError($resultCode),
            'is_risk_management_issue' => $this->isRiskManagementIssue($resultCode),
            'is_communication_error' => $this->isCommunicationError($resultCode),
            'is_system_error' => $this->isSystemError($resultCode),
            'is_chargeback' => $this->isChargeback($resultCode),
            'is_soft_decline' => $this->isSoftDecline($resultCode),
        ];
    }

    /**
     * Check if transaction can be retried based on result code
     */
    public function canRetry(string $resultCode): bool
    {
        // Generally, these types of errors can be retried
        $retryableCategories = [
            'communication_error',
            'system_error',
            'soft_decline',
        ];

        $category = $this->getCategory($resultCode);

        // Specific codes that can be retried
        $retryableCodes = [
            '800.100.176', // account temporarily not available
            '900.100.500', // timeout at connectors/acquirer side (try later)
            '900.100.600', // connector/acquirer currently down
            '800.800.400', // Connector/acquirer system is under maintenance
            '800.800.800', // The payment system is currently unavailable
            '800.800.801', // The payment system is currently under maintenance
        ];

        return in_array($category, $retryableCategories) || in_array($resultCode, $retryableCodes);
    }

    /**
     * Get suggested action based on result code
     */
    public function getSuggestedAction(string $resultCode): string
    {
        $category = $this->getCategory($resultCode);

        switch ($category) {
            case 'successful':
                return 'Transaction completed successfully. Process the order.';

            case 'successful_review_required':
                return 'Transaction successful but requires manual review due to risk factors.';

            case 'pending':
                return 'Transaction is pending. Wait for status update or check transaction status later.';

            case 'bank_declined':
                return 'Transaction declined by bank. Customer should contact their bank or try a different payment method.';

            case '3d_secure_issue':
                return '3D Secure authentication issue. Customer may need to retry with proper authentication.';

            case 'blacklisted':
                return 'Account, card, or customer is blacklisted. Transaction cannot be processed.';

            case 'validation_error':
                return 'Invalid input data. Check and correct the payment information.';

            case 'configuration_error':
                return 'System configuration issue. Contact technical support.';

            case 'risk_management_issue':
                return 'Transaction flagged by risk management. Review transaction or contact customer.';

            case 'communication_error':
                return 'Communication error with payment processor. Retry the transaction.';

            case 'system_error':
                return 'System error occurred. Retry later or contact support if issue persists.';

            case 'soft_decline':
                return 'Additional customer authentication required. Implement Strong Customer Authentication (SCA).';

            case 'chargeback':
                return 'Chargeback initiated. Review chargeback details and respond appropriately.';

            default:
                return 'Unknown error. Contact technical support for assistance.';
        }
    }

    /**
     * Parse result code structure
     */
    public function parseResultCode(string $resultCode): array
    {
        $parts = explode('.', $resultCode);

        if (count($parts) !== 3) {
            return [
                'valid' => false,
                'error' => 'Invalid result code format',
            ];
        }

        return [
            'valid' => true,
            'main_group' => $parts[0],
            'sub_group' => $parts[1],
            'specific_code' => $parts[2],
            'full_code' => $resultCode,
        ];
    }
}
