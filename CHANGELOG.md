# Changelog

All notable changes to `laravel-hyperpay-gateway` will be documented in this file.

## [1.0.0] - 2025-06-26

### Added
- Initial release of Laravel HyperPay Gateway package
- HyperPay service integration with checkout and payment processing
- Support for multiple payment brands (VISA, MASTER, MADA, AMEX, APPLEPAY, STCPAY)
- Payment and credit card models with database migrations
- Webhook handling for payment status updates
- Event system for payment lifecycle (successful, failed, pending, etc.)
- Facade for easy service access
- Artisan commands for installation and status checking
- Blade views for payment result pages
- Comprehensive test suite
- Card tokenization support
- User traits for payment and credit card relationships
