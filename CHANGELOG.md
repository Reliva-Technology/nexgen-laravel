# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [1.0.2] - 2026-02-23

### Changed
- Composer.json versioning and configuration options
- README clarity on features and installation
- LICENSE year to 2026
- NexgenClient and NexgenQRClient constructors for better configuration handling
- Removed unused `NexgenCollectionStatus` enum and related code
- composer.lock dependencies

## [1.0.1] - 2026-01-06

### Added
- `NexgenQRClient` for dynamic QR code generation and terminal management
- Terminal management: create terminal, get terminal list, get terminal data, get terminal data with billing list
- Dynamic QR: create dynamic QR for payments, get QR code data by code
- Environment variable validation in `NexgenClient` and `NexgenQRClient` with clear errors for missing keys
- Separate production and sandbox API keys in config; clients select keys by environment
- QR configuration: `QR_ENVIRONMENT`, `QR_TERMINAL_CODE`, `QR_CALLBACK_URL`, `QR_ENDPOINT`
- README details on configuration validation and required API keys per environment

### Changed
- Removed `NexgenEnvironment` enum; environments use string values (`sandbox`, `production`, `custom`)
- `NexgenClient` and `NexgenQRClient` constructors accept string environment
- Config and service provider: environment-based key selection, QR API settings
- NexgenQRClient create terminal request field names: `fieldName`, `fieldDescription`, `fieldStatus`
- Dynamic QR request field names use `field` prefix for consistency
- NexgenQRClient response handling returns JSON data directly in `NexgenResponse`
- README examples use manual instantiation instead of dependency injection
- Composer description spelling: "Nexgen" → "NexGen"

### Removed
- `NexgenCollectionStatus` enum (replaced by string usage where needed)
- Configuration dependency from service bindings in `NexgenServiceProvider`

## [1.0.0] - 2025-11-17

### Added
- Initial release of Nexgen Payment Gateway Integration for Laravel
- `NexgenClient` for Nexgen API (collections and billings)
- Collection management: create, get list, get data, get data with billing list, switch status
- Billing management: create billings with full payment details, get billing data by ID
- Environments: sandbox, production, custom endpoint
- Publishable config, env support, default collection code, callback URL, redirect URL
- Laravel service provider and auto-discovery
- `NexgenCollectionStatus` enum (active, inactive)
- DTOs: `NexgenCreateCollection`, `NexgenCreateBilling` with optional fields
- `NexgenResponse` for API response handling
- External references (up to 4 fields), per-billing callback and redirect URLs
- Error handling, validation, and documentation

### Security
- API key and secret authentication
- Secure API request handling with headers

---

[Unreleased]: https://github.com/Reliva-Technology/nexgen-laravel/compare/v1.0.2...HEAD
[1.0.2]: https://github.com/Reliva-Technology/nexgen-laravel/compare/v1.0.1...v1.0.2
[1.0.1]: https://github.com/Reliva-Technology/nexgen-laravel/compare/v1.0.0...v1.0.1
[1.0.0]: https://github.com/Reliva-Technology/nexgen-laravel/releases/tag/v1.0.0
