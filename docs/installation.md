# Installation

This guide covers installing and configuring the Nexgen Payment Gateway package in your Laravel application. It is **step 1** in the documentation flow.

## Table of contents

| # | Document | Description |
|---|----------|-------------|
| 1 | [**Installation**](installation.md) *(you are here)* | Install and configure the package |
| 2 | [**Usage**](usage.md) | NexgenClient & NexgenQRClient setup and usage |
| 3 | [**DuitNow**](duitnow.md) | Collections & billings (link-based payments) |
| 4 | [**DuitNow QR**](duitnow-qr.md) | Terminals & dynamic QR payments |
| 5 | [**Webhooks**](webhooks.md) | Handling payment callbacks |

## Requirements

- **PHP** >= 8.1
- **Laravel** >= 8.0
- **Composer**

## Step 1: Install the package

Install via Composer:

```bash
composer require reliva/nexgen
```

The package will register its service provider automatically (Laravel package discovery).

## Step 2: Publish configuration

Publish the configuration file to your `config` directory:

```bash
php artisan vendor:publish --tag=nexgen-config
```

This creates `config/nexgen.php`. You can edit this file if you need to change defaults; values are typically driven by environment variables.

## Step 3: Configure environment variables

Add the following to your `.env` file and replace placeholders with your Nexgen dashboard credentials.

### Collections & Billings (main API)

```env
# Nexgen API Configuration
NEXGEN_ENVIRONMENT=sandbox
NEXGEN_PROD_API_KEY=your_production_api_key_here
NEXGEN_PROD_API_SECRET=your_production_api_secret_here
NEXGEN_SANDBOX_API_KEY=your_sandbox_api_key_here
NEXGEN_SANDBOX_API_SECRET=your_sandbox_api_secret_here
NEXGEN_COLLECTION_CODE=your_collection_code_here
NEXGEN_CALLBACK_URL=https://your-domain.com/callback
NEXGEN_REDIRECT_URL=https://your-domain.com/redirect
NEXGEN_CUSTOM_ENDPOINT=https://custom-endpoint.com
```

### QR Code payments (optional)

Only required if you use the QR / terminal features:

```env
# QR Code Payment Configuration
NEXGEN_QR_ENVIRONMENT=production
NEXGEN_QR_PROD_API_KEY=your_qr_production_api_key_here
NEXGEN_QR_PROD_API_SECRET=your_qr_production_api_secret_here
NEXGEN_QR_TERMINAL_CODE=your_terminal_code_here
NEXGEN_QR_CALLBACK_URL=https://your-domain.com/qr-callback
NEXGEN_QR_CUSTOM_ENDPOINT=https://custom-qr-endpoint.com
```

### Configuration reference

| Variable | Description | Required | Default |
|----------|-------------|----------|---------|
| `NEXGEN_ENVIRONMENT` | Environment: `sandbox`, `production`, or `custom` | Yes | `sandbox` |
| `NEXGEN_PROD_API_KEY` | Nexgen Production API key | Yes* | - |
| `NEXGEN_PROD_API_SECRET` | Nexgen Production API secret | Yes* | - |
| `NEXGEN_SANDBOX_API_KEY` | Nexgen Sandbox API key | Yes* | - |
| `NEXGEN_SANDBOX_API_SECRET` | Nexgen Sandbox API secret | Yes* | - |
| `NEXGEN_COLLECTION_CODE` | Default collection code | No | - |
| `NEXGEN_CALLBACK_URL` | Default webhook callback URL | No | - |
| `NEXGEN_REDIRECT_URL` | Default redirect URL after payment | No | - |
| `NEXGEN_CUSTOM_ENDPOINT` | Custom API base URL (only when `NEXGEN_ENVIRONMENT=custom`) | No | - |
| `NEXGEN_QR_ENVIRONMENT` | QR API environment: `production` or `custom` | No | `production` |
| `NEXGEN_QR_PROD_API_KEY` | Nexgen QR Production API key | Yes* (for QR) | - |
| `NEXGEN_QR_PROD_API_SECRET` | Nexgen QR Production API secret | Yes* (for QR) | - |
| `NEXGEN_QR_TERMINAL_CODE` | Default terminal code for QR | No | - |
| `NEXGEN_QR_CALLBACK_URL` | Default QR webhook callback URL | No | - |
| `NEXGEN_QR_CUSTOM_ENDPOINT` | Custom QR API base URL (only when QR env is `custom`) | No | - |

\* **Required by environment:**  
- **Sandbox:** set `NEXGEN_SANDBOX_API_KEY` and `NEXGEN_SANDBOX_API_SECRET`.  
- **Production / Custom:** set `NEXGEN_PROD_API_KEY` and `NEXGEN_PROD_API_SECRET`.  
- **QR features:** set `NEXGEN_QR_PROD_API_KEY` and `NEXGEN_QR_PROD_API_SECRET` for the QR client.

## How keys are selected

- **Main API (`NexgenClient`):**  
  - `NEXGEN_ENVIRONMENT=sandbox` → uses sandbox key/secret.  
  - `NEXGEN_ENVIRONMENT=production` or `custom` → uses production key/secret.

- **QR API (`NexgenQRClient`):**  
  - Uses `NEXGEN_QR_PROD_API_KEY` and `NEXGEN_QR_PROD_API_SECRET` for both `production` and `custom` (no sandbox for QR).

The package validates that the required variables for the chosen environment are set when you create a client. If something is missing, an `InvalidArgumentException` is thrown with a message indicating which variables to set.

## Verify installation

1. **Clear config cache** (if you use `php artisan config:cache` in production):

   ```bash
   php artisan config:clear
   ```

2. **Use the client in code** (e.g. in a controller or route):

   ```php
    protected NexgenQRClient $client;

    public function __construct()
    {
        $this->client = new NexgenQRClient();
    }
   $response = $client->getCollectionList();
   if ($response->isSuccess()) {
       // Installation and config are working
   }
   ```

If you see an `InvalidArgumentException` about missing environment variables, ensure the correct keys and secrets are set in `.env` for the environment you use.

## Next steps

1. **[Usage](usage.md)** — Configure and use `NexgenClient` (and optionally `NexgenQRClient`). Do this next.
2. **Choose your payment flow:**
   - [DuitNow (collections/billings)](duitnow.md) — Link-based payments: collections, billings, payment URL.
   - [DuitNow QR](duitnow-qr.md) — QR code payments: terminals, dynamic QR.

For webhooks and error handling, see [Webhooks](webhooks.md) and [Usage](usage.md).
