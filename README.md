# Nexgen Payment Gateway Integration for Laravel

Laravel package for the Nexgen Payment Gateway API: Online Banking, MPGS and Duitnow Dynamic QR.

## Features

- **Collections & billings** — Create collections, create billings, get payment URLs for Online Banking / MPGS
- **QR payments** — Terminals and dynamic QR codes for Duitnow Dynamic QR
- **Environments** — Sandbox, production, and custom (Online banking / MPGS); production and custom (Duitnow Dynamic QR)
- PHP 8.1+, Laravel 8.0+, API key/secret authentication

## Requirements

- PHP >= 8.1
- Laravel >= 8.0
- Composer

## Installation

```bash
composer require reliva/nexgen
php artisan vendor:publish --tag=nexgen-config
```

Add the required environment variables to `.env` (see [docs/installation.md](docs/installation.md) for the full list and options).

## Quick start

**Collections & billings (Online Banking / MPGS):**

```php
use Reliva\Nexgen\NexgenClient;

    protected NexgenClient $client;

    public function __construct()
    {
        $this->client = new NexgenClient();
    }
 
```

**QR payments (DuitNow Dynamic QR):**

```php
use Reliva\Nexgen\NexgenQRClient;

    protected NexgenQRClient $client;

    public function __construct()
    {
        $this->client = new NexgenQRClient();
    }
```

## Documentation

Full documentation is in the [`docs/`](docs/) folder. Recommended flow:


| # | Document | Description |
|---|----------|-------------|
| 1 | [**Installation**](docs/installation.md) | Install and configure the package |
| 2 | [**Usage**](docs/usage.md) | NexgenClient & NexgenQRClient setup and usage |
| 3 | [**DuitNow**](docs/duitnow.md) | Collections & billings (link-based payments) |
| 4 | [**DuitNow QR**](docs/duitnow-qr.md) | Terminals & dynamic QR payments |
| 5 | [**Webhooks**](docs/webhooks.md) | Handling payment callbacks |

The docs cover all API methods, parameters, response handling, and error handling.

## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Changelog

See [CHANGELOG](CHANGELOG.md) for changes.
