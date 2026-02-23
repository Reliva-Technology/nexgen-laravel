# DuitNow QR

This guide describes the **QR code payment flow** for DuitNow using terminals and dynamic QR codes. It is **step 3** in the documentation flow. Complete [Installation](installation.md) and [Usage](usage.md) first.

## Table of contents

| # | Document | Description |
|---|----------|-------------|
| 1 | [**Installation**](installation.md) | Install and configure the package |
| 2 | [**Usage**](usage.md) | NexgenClient & NexgenQRClient setup and usage |
| 3 | [**DuitNow**](duitnow.md) | Collections & billings (link-based payments) |
| 4 | [**DuitNow QR**](duitnow-qr.md) *(you are here)* | Terminals & dynamic QR payments |
| 5 | [**Webhooks**](webhooks.md) | Handling payment callbacks |

Use `NexgenQRClient` to manage terminals and create dynamic QR codes that customers scan to pay. For the link-based flow (collections and billings), use [DuitNow (collections/billings)](duitnow.md) instead.

## Overview

- **Terminals** — Physical or virtual payment points where QR codes are generated. You create terminals, then create dynamic QR transactions under a terminal.
- **Dynamic QR** — A one-off QR code for a specific amount and description. Creating a dynamic QR returns a `qr_code` (base64 image string) to display to the customer; they scan it to pay via DuitNow.

**Note:** The QR API has no sandbox. Supported environments are `production` and `custom`. Use `NEXGEN_QR_PROD_API_KEY` and `NEXGEN_QR_PROD_API_SECRET` (different from the main API keys).

## Client

Resolve the QR client from the container or instantiate it manually:

```php
use Reliva\Nexgen\NexgenQRClient;

$nexgenQR = app('nexgen-qr');
// or: $nexgenQR = new NexgenQRClient();
```

Configuration is read from `config('nexgen.QR_*')`. Required in `.env` for QR:

- `NEXGEN_QR_ENVIRONMENT` — `production` or `custom`
- `NEXGEN_QR_PROD_API_KEY` and `NEXGEN_QR_PROD_API_SECRET`

Optional: `NEXGEN_QR_TERMINAL_CODE`, `NEXGEN_QR_CALLBACK_URL`, `NEXGEN_QR_CUSTOM_ENDPOINT` (when environment is `custom`). Full list: [Installation](installation.md). For instantiating the client: [Usage](usage.md).

---

## API methods (NexgenQRClient)

### createTerminal

Create a new terminal. Terminals are payment points under which you create dynamic QR codes.

**Signature:**

```php
public function createTerminal(NexgenCreateTerminal $createTerminal)
```

**Example:**

```php
use Reliva\Nexgen\NexgenCreateTerminal;

$createTerminal = new NexgenCreateTerminal(
    name: 'Store Counter 1',
    description: 'Main checkout counter'
);

$response = $nexgenQR->createTerminal($createTerminal);

if ($response->isSuccess()) {
    $data = $response->getData();
    $terminalCode = $data['code'] ?? null;
}
```

**Name & description:** Required; 5–50 characters; only letters, digits, hyphens, and spaces (same rules as collections).

---

### getTerminalList

Retrieve a list of all terminals for your account.

**Signature:**

```php
public function getTerminalList()
```

**Example:**

```php
$response = $nexgenQR->getTerminalList();

if ($response->isSuccess()) {
    $terminals = $response->getData();
}
```

---

### getTerminal

Get detailed data for one terminal by its code. If `$terminalCode` is omitted, the client uses the configured default terminal code.

**Signature:**

```php
public function getTerminal(?string $terminalCode = null)
```

**Example:**

```php
$response = $nexgenQR->getTerminal('RLVT2TEA0001');
// or use default: $nexgenQR->getTerminal(null);

if ($response->isSuccess()) {
    $terminal = $response->getData();
}
```

---

### getTerminalDataBilling

Get terminal data plus all QR billings (transactions) for that terminal. If `$terminalCode` is omitted, the configured default is used.

**Signature:**

```php
public function getTerminalDataBilling(?string $terminalCode = null)
```

**Example:**

```php
$response = $nexgenQR->getTerminalDataBilling('RLVT2TEA0001');

if ($response->isSuccess()) {
    $data = $response->getData();
    $terminal = $data['terminal'] ?? null;
    $billings = $data['bill_list'] ?? [];
}
```

---

### createDynamicQR

Create a dynamic QR code for a specific amount and description. Returns data including `code`, `status`, `amount`, `payment_description`, `due_date`, `callback_url`, and **`qr_code`** (a base64-encoded image string to display as a QR for the customer to scan). If `$terminalCode` is omitted, the client uses the configured default. If `fieldCallbackUrl` is omitted on the DTO, the client’s default callback URL is used.

**Signature:**

```php
public function createDynamicQR(
    NexgenCreateDynamicQR $createDynamicQR,
    ?string $terminalCode = null
)
```

**Response format:**

The API returns a JSON object with the following fields:

| Field | Type | Description |
|-------|------|-------------|
| `code` | string | Unique QR transaction code (e.g. for webhooks or `getQRData`) |
| `status` | string | e.g. `"unpaid"` until the customer pays |
| `amount` | string | Payment amount |
| `payment_description` | string | Description of the payment |
| `due_date` | string | Due date/time for the QR |
| `callback_url` | string | Webhook URL for this QR |
| `qr_code` | string | **Base64-encoded** QR image (decode and display as image for customer to scan) |

**Example response:**

```json
{
  "code": "RLVQZZD260223A0020",
  "status": "unpaid",
  "amount": "1.00",
  "payment_description": "test billing",
  "due_date": "23-02-2026 12:27:00",
  "callback_url": "https://webhook.site/d6637e28-ff2c-49b3-8be3-e38726fde500",
  "qr_code": "iVBORw...Jggg=="
}
```

**Example:**

```php
use Reliva\Nexgen\NexgenCreateDynamicQR;

$createDynamicQR = new NexgenCreateDynamicQR(
    fieldAmount: '50.00',
    fieldPaymentDescription: 'Purchase at Store Counter 1',
    fieldCallbackUrl: 'https://your-domain.com/qr-webhook',
    fieldExternalReferenceLabel1: 'Order ID',
    fieldExternalReferenceValue1: 'ORD-12345',
    fieldExternalReferenceLabel2: 'Customer ID',
    fieldExternalReferenceValue2: 'CUST-789'
);

$response = $nexgenQR->createDynamicQR($createDynamicQR, 'RLVT2TEA0001');

if ($response->isSuccess()) {
    $data = $response->getData();
    $code       = $data['code'] ?? null;       // use for webhooks / getQRData
    $status     = $data['status'] ?? null;    // e.g. 'unpaid'
    $qrCodeB64  = $data['qr_code'] ?? null;   // base64 QR image string
    // Decode and display $qrCodeB64 as an image for the customer to scan
    // e.g. <img src="data:image/png;base64,<?= $qrCodeB64 ?>" />
}
```

**Dynamic QR parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `fieldAmount` | string | Yes | Payment amount (e.g. `"50.00"`) |
| `fieldPaymentDescription` | string | Yes | Description of the payment |
| `fieldCallbackUrl` | string | No | Webhook URL (uses client default if not set) |
| `fieldExternalReferenceLabel1` | string | No | Custom reference label #1 |
| `fieldExternalReferenceValue1` | string | No | Value for label #1 (use with label #1) |
| `fieldExternalReferenceLabel2` | string | No | Custom reference label #2 |
| `fieldExternalReferenceValue2` | string | No | Value for label #2 (use with label #2) |

---

### getQRData

Get detailed data for a specific QR transaction by QR code and (optional) terminal code. If `$terminalCode` is omitted, the configured default is used.

**Signature:**

```php
public function getQRData(string $qr_code, ?string $terminalCode = null)
```

**Example:**

```php
$qrCode       = 'RLVQ2UE241003AWLB4';
$terminalCode = 'RLVT2TEA0001'; // optional

$response = $nexgenQR->getQRData($qrCode, $terminalCode);

if ($response->isSuccess()) {
    $qrData = $response->getData();
    $status = $qrData['status'] ?? null; // e.g. 'paid', 'pending', 'expired'
}
```

---

## Helper methods

| Method | Return | Description |
|--------|--------|-------------|
| `getEndpoint()` | `string` | Current API base URL |
| `getEnvironment()` | `string` | `production` or `custom` |
| `getTerminalCode()` | `string` | Default terminal code |
| `getCallbackUrl()` | `string` | Default callback URL |
| `getApiKey()` | `string` | API key in use |
| `getApiSecret()` | `string` | API secret in use |
| `getConfig()` | `array` | Full runtime config (api_key, api_secret, environment, endpoint, terminal_code, callback_url) |

---

## Typical DuitNow QR flow

1. **Create a terminal** (once per location/counter) with `createTerminal()`; store the returned `code`.
2. **Create a dynamic QR** with `createDynamicQR($createDynamicQR, $terminalCode)`; use the returned `qr_code` (base64 string) to display a QR image for the customer to scan.
3. **Customer scans** the QR and pays via DuitNow (or other supported methods).
4. **Handle webhook** at your `callbackUrl` when the QR payment completes (see [Webhooks](webhooks.md)).
5. **Optional:** List terminals with `getTerminalList()`, inspect one with `getTerminal()` or `getTerminalDataBilling()`, and check a specific QR with `getQRData()`.

All methods return a `NexgenResponse`; use `$response->isSuccess()` and `$response->getData()` (see [Usage](usage.md)#error-handling). If required QR config is missing, the client throws `InvalidArgumentException` when instantiated.

---

## See also

- [Installation](installation.md) — Step 1
- [Usage](usage.md) — Step 2: config and client usage
- [DuitNow (collections/billings)](duitnow.md) — Alternative: link-based DuitNow payments
- [Webhooks](webhooks.md) — Webhook handler and payload
