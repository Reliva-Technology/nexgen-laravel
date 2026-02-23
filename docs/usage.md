# Usage

This guide explains how to use **NexgenClient** (collections/billings) and **NexgenQRClient** (QR payments), including configuration and instantiation. It is **step 2** in the documentation flow. Complete [Installation](installation.md) first.

## Table of contents

| # | Document | Description |
|---|----------|-------------|
| 1 | [**Installation**](installation.md) | Install and configure the package |
| 2 | [**Usage**](usage.md) *(you are here)* | NexgenClient & NexgenQRClient setup and usage |
| 3 | [**DuitNow**](duitnow.md) | Collections & billings (link-based payments) |
| 4 | [**DuitNow QR**](duitnow-qr.md) | Terminals & dynamic QR payments |
| 5 | [**Webhooks**](webhooks.md) | Handling payment callbacks |

## Configuration

### 1. Publish config

If you haven’t already, publish the Nexgen config (see [Installation](installation.md)). Then you can rely on environment-based defaults:

```bash
php artisan vendor:publish --tag=nexgen-config
```

This creates `config/nexgen.php`. Values are usually driven by `.env`.

### 2. Environment variables

Add these to your `.env` for **NexgenClient** (collections/billings):

```env
# Required: environment (sandbox | production | custom)
NEXGEN_ENVIRONMENT=sandbox

# Required by environment:
NEXGEN_SANDBOX_API_KEY=your_sandbox_api_key_here
NEXGEN_SANDBOX_API_SECRET=your_sandbox_api_secret_here
NEXGEN_PROD_API_KEY=your_production_api_key_here
NEXGEN_PROD_API_SECRET=your_production_api_secret_here

# Optional defaults (can be overridden per request or in constructor)
NEXGEN_COLLECTION_CODE=your_collection_code_here
NEXGEN_CALLBACK_URL=https://your-domain.com/callback
NEXGEN_REDIRECT_URL=https://your-domain.com/redirect

# Only when NEXGEN_ENVIRONMENT=custom
NEXGEN_CUSTOM_ENDPOINT=https://custom-endpoint.com
```

### 3. How configuration is applied

| Source | What it does |
|--------|----------------|
| `config/nexgen.php` | Reads from `.env` and exposes `ENVIRONMENT`, `API_KEY`, `API_SECRET`, `ENDPOINT`, `COLLECTION_CODE`, `CALLBACK_URL`, `REDIRECT_URL`. |
| `API_KEY` / `API_SECRET` | Resolved in config: **sandbox** → `NEXGEN_SANDBOX_*`, **production** or **custom** → `NEXGEN_PROD_*`. |
| `NexgenClient` constructor | If you omit a parameter, the client uses the corresponding value from `config('nexgen.*')`. |

So for NexgenClient:

- **Sandbox:** set `NEXGEN_ENVIRONMENT=sandbox` and both `NEXGEN_SANDBOX_API_KEY` and `NEXGEN_SANDBOX_API_SECRET`.
- **Production:** set `NEXGEN_ENVIRONMENT=production` and both `NEXGEN_PROD_API_KEY` and `NEXGEN_PROD_API_SECRET`.
- **Custom:** set `NEXGEN_ENVIRONMENT=custom`, production keys, and `NEXGEN_CUSTOM_ENDPOINT`.

If required keys are missing when the client is instantiated, the client throws an `InvalidArgumentException` stating which variables to set.

### 4. Configuration reference (NexgenClient)

| Config key (in code) | .env variable | Required | Description |
|----------------------|---------------|----------|-------------|
| `ENVIRONMENT` | `NEXGEN_ENVIRONMENT` | Yes | `sandbox`, `production`, or `custom` |
| (resolved from env) | `NEXGEN_SANDBOX_API_KEY` | Yes (sandbox) | Sandbox API key |
| (resolved from env) | `NEXGEN_SANDBOX_API_SECRET` | Yes (sandbox) | Sandbox API secret |
| (resolved from env) | `NEXGEN_PROD_API_KEY` | Yes (prod/custom) | Production API key |
| (resolved from env) | `NEXGEN_PROD_API_SECRET` | Yes (prod/custom) | Production API secret |
| `COLLECTION_CODE` | `NEXGEN_COLLECTION_CODE` | No | Default collection code |
| `CALLBACK_URL` | `NEXGEN_CALLBACK_URL` | No | Default webhook callback URL |
| `REDIRECT_URL` | `NEXGEN_REDIRECT_URL` | No | Default redirect after payment |
| `ENDPOINT` | `NEXGEN_CUSTOM_ENDPOINT` | When custom | API base URL for custom environment |

---

## Instantiating NexgenClient

### Using the container (recommended)

The package registers a binding so you can resolve the client with config-based defaults:


```php
public function __construct(
    private NexgenClient $client
) {}
```

When created this way, the client uses:

- `config('nexgen.ENVIRONMENT')`
- `config('nexgen.API_KEY')` and `config('nexgen.API_SECRET')` (already chosen by environment in config)
- `config('nexgen.COLLECTION_CODE')`, `config('nexgen.CALLBACK_URL')`, `config('nexgen.REDIRECT_URL')`

No need to pass API key/secret unless you want to override them.

### Manual instantiation

You can create the client yourself and override only what you need. Omitted parameters fall back to config.

**Use config for keys (recommended):**

```php
use Reliva\Nexgen\NexgenClient;

$nexgen = new NexgenClient(
    apiKey: null,        // use config
    apiSecret: null,     // use config
    environment: 'production',
    collectionCode: 'RLVC2TEA0002',
    callbackUrl: 'https://your-domain.com/webhook',
    redirectUrl: 'https://your-domain.com/thank-you'
);
```

**Override keys explicitly:**

```php
$nexgen = new NexgenClient(
    apiKey: 'your_api_key',
    apiSecret: 'your_api_secret',
    environment: 'production',
    collectionCode: 'your_collection_code',
    callbackUrl: 'https://your-domain.com/callback',
    redirectUrl: 'https://your-domain.com/redirect'
);
```

Constructor parameters (all optional):

| Parameter | Type | Default | Description |
|-----------|------|---------|-------------|
| `$apiKey` | `?string` | `config('nexgen.API_KEY')` | API key (null = use config) |
| `$apiSecret` | `?string` | `config('nexgen.API_SECRET')` | API secret (null = use config) |
| `$environment` | `?string` | `config('nexgen.ENVIRONMENT')` | `sandbox`, `production`, or `custom` |
| `$collectionCode` | `?string` | `config('nexgen.COLLECTION_CODE')` | Default collection code |
| `$callbackUrl` | `?string` | `config('nexgen.CALLBACK_URL')` | Default webhook URL |
| `$redirectUrl` | `?string` | `config('nexgen.REDIRECT_URL')` | Default post-payment redirect URL |

---

## Environments and endpoints

The client sets the API base URL from the environment:

| Environment | Endpoint |
|-------------|----------|
| `sandbox` | `https://dash-nexgen-stg.reliva.com.my` |
| `production` | `https://dash-nexgen.reliva.com.my` |
| `custom` | Value of `config('nexgen.ENDPOINT')` (e.g. `NEXGEN_CUSTOM_ENDPOINT`) |

For `custom`, you must set `NEXGEN_CUSTOM_ENDPOINT` (or the config `ENDPOINT`) so the client has a valid base URL.

---

## NexgenQRClient (QR payments)

For **DuitNow QR** (terminals and dynamic QR), use `NexgenQRClient`. It uses separate config: `NEXGEN_QR_ENVIRONMENT`, `NEXGEN_QR_PROD_API_KEY`, `NEXGEN_QR_PROD_API_SECRET`, and optionally `NEXGEN_QR_TERMINAL_CODE`, `NEXGEN_QR_CALLBACK_URL`. No sandbox; only `production` or `custom`. See [Installation](installation.md) for QR env vars.

**Resolve from container:**

```php
use Reliva\Nexgen\NexgenQRClient;

$nexgenQR = app('nexgen-qr');
```

**Manual instantiation** (omit params to use config):

```php
$nexgenQR = new NexgenQRClient(
    apiKey: null,           // use config('nexgen.QR_API_KEY')
    apiSecret: null,         // use config('nexgen.QR_API_SECRET')
    environment: 'production',
    terminalCode: 'RLVT2TEA0001',
    callbackUrl: 'https://your-domain.com/qr-webhook'
);
```

For full QR API methods and flow, see [DuitNow QR](duitnow-qr.md).

---

## Helper methods (NexgenClient)

Use these to inspect the current client state:

| Method | Return | Description |
|--------|--------|-------------|
| `getEndpoint()` | `string` | Current API base URL |
| `getEnvironment()` | `string` | Current environment |
| `isSandbox()` | `bool` | Whether environment is sandbox |
| `getApiKey()` | `string` | API key in use |
| `getApiSecret()` | `string` | API secret in use |
| `getCollectionCode()` | `string` | Default collection code |
| `getConfig()` | `array` | Full runtime config (api_key, api_secret, environment, endpoint, collection_code, callback_url, redirect_url) |

Example:

```php
$nexgen = app('nexgen');

if ($nexgen->isSandbox()) {
    // Use test data or sandbox-only logic
}

$endpoint = $nexgen->getEndpoint();
$defaultCollection = $nexgen->getCollectionCode();
```

---

## API methods (overview)

**NexgenClient** — All of these return a `NexgenResponse`. Use `$response->isSuccess()` and `$response->getData()` to handle results. See [DuitNow (collections/billings)](duitnow.md) for full examples and flow.

| Method | Purpose |
|--------|---------|
| `createCollection(NexgenCreateCollection $createCollection)` | Create a new collection |
| `getCollectionList()` | List all collections |
| `getCollection(?string $collectionCode = null)` | Get one collection (null = default code) |
| `getCollectionDataBilling(?string $collectionCode = null)` | Get collection plus its billing list |
| `createBilling(NexgenCreateBilling $createBilling, ?string $collectionCode = null)` | Create a billing; returns `payment_url` for DuitNow |
| `getBillingData(string $billingId, ?string $collectionCode = null)` | Get one billing by ID |

Where `$collectionCode` is optional, the client uses its default collection code when you pass `null` or omit the argument.

---

## Error handling

**Configuration validation:** If required env vars are missing for the chosen environment, the constructor throws `\InvalidArgumentException` with a message listing the missing variables (e.g. `NEXGEN_SANDBOX_API_KEY`, `NEXGEN_SANDBOX_API_SECRET` for sandbox).

**API responses:** Always check `$response->isSuccess()` before using `$response->getData()`. On failure, `getData()` contains error details (e.g. `['error' => '...']`).

```php
$response = $nexgen->createBilling($billing, $collectionCode);

if (!$response->isSuccess()) {
    $error = $response->getData();
    // Handle $error['error'] or other keys
    return back()->withErrors(['payment' => $error['error'] ?? 'Payment failed']);
}
```

---

## Next steps

Choose your payment flow (step 3):

- **[DuitNow (collections/billings)](duitnow.md)** — Full flow with `NexgenClient`: collections, billings, payment URL for link-based DuitNow.
- **[DuitNow QR](duitnow-qr.md)** — Full flow with `NexgenQRClient`: terminals, dynamic QR for scan-to-pay.

## See also

- [Installation](installation.md) — Step 1: install and first-time config
- [Webhooks](webhooks.md) — Webhook handler and payload
