# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

### Added
- `NexgenQRClient` class for QR code payment functionality
- Terminal management:
  - Create new terminals
  - Get terminal list
  - Get terminal data
  - Get terminal data with billing list
- Dynamic QR code generation:
  - Create dynamic QR codes for payments
  - Get QR code data by code
- QR-specific configuration options:
  - `QR_ENVIRONMENT` (production or custom)
  - `QR_TERMINAL_CODE`
  - `QR_CALLBACK_URL`
  - `QR_ENDPOINT` for custom environments

### Changed
- Removed `NexgenEnvironment` enum - environments now use string values ('sandbox', 'production', 'custom')
- `NexgenClient` constructor now accepts string environment instead of enum

## [1.0.0] - 2024-12-XX

### Added
- Initial release of Nexgen Payment Gateway Integration for Laravel
- `NexgenClient` class for interacting with Nexgen API
- Collection management:
  - Create new collections
  - Get collection list
  - Get collection data
  - Get collection data with billing list
  - Switch collection status (active/inactive)
- Billing management:
  - Create new billings with full payment details
  - Get billing data by ID
- Environment support:
  - Sandbox environment
  - Production environment
  - Custom endpoint support
- Configuration system:
  - Publishable configuration file
  - Environment variable support
  - Default collection code, callback URL, and redirect URL configuration
- Service provider for Laravel auto-discovery
- Type-safe enums:
  - `NexgenCollectionStatus` enum (active, inactive)
- Data transfer objects:
  - `NexgenCreateCollection` for collection creation
  - `NexgenCreateBilling` for billing creation with all optional fields
- `NexgenResponse` class for standardized API response handling
- Support for external references (up to 4 custom reference fields)
- Support for custom callback and redirect URLs per billing
- Comprehensive error handling and validation
- Full documentation with examples

### Security
- API key and secret authentication
- Secure API request handling with proper headers

[Unreleased]: https://github.com/reliva/nexgen-laravel-package/compare/v1.0.0...HEAD
[1.0.0]: https://github.com/reliva/nexgen-laravel-package/releases/tag/v1.0.0

