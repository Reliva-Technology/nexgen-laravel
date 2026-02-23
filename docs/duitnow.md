# DuitNow (Collections & Billings)

This guide describes the DuitNow payment flow using **collections** and **billings** via the main Nexgen API. It is **step 3** in the documentation flow. Complete [Installation](installation.md) and [Usage](usage.md) first.

## Table of contents

| # | Document | Description |
|---|----------|-------------|
| 1 | [**Installation**](installation.md) | Install and configure the package |
| 2 | [**Usage**](usage.md) | NexgenClient & NexgenQRClient setup and usage |
| 3 | [**DuitNow**](duitnow.md) *(you are here)* | Collections & billings (link-based payments) |
| 4 | [**DuitNow QR**](duitnow-qr.md) | Terminals & dynamic QR payments |
| 5 | [**Webhooks**](webhooks.md) | Handling payment callbacks |

Use `NexgenClient` to create collections, create billings, and retrieve data. Customers pay via DuitNow Online Banking/Wallet using the payment URL returned when you create a billing. For the QR-based flow (terminals and dynamic QR), use [DuitNow QR](duitnow-qr.md) instead.

## Overview

- **Collections** — Groups of related bills (e.g. “Service Fees”, “Subscription Payments”). Every billing must belong to a collection.
- **Billings** — Payment requests for customers. Each billing has payer details, amount, description, optional due date, redirect/callback URLs, and optional external references. Creating a billing returns a `payment_url` to send the customer to complete payment via DuitNow.

## Client

Use the same client and configuration as in [Usage](usage.md):

```php
use Reliva\Nexgen\NexgenClient;

$nexgen = app('nexgen');
// or: $nexgen = new NexgenClient();
```

---

## API methods (NexgenClient)

### createCollection

Create a new collection. A collection is a group of related bills (e.g. “Service Fees”, “Rental Payments”). Each bill must be associated with a collection.

**Signature:**

```php
public function createCollection(NexgenCreateCollection $createCollection)
```

**Example:**

```php
use Reliva\Nexgen\NexgenCreateCollection;

$createCollection = new NexgenCreateCollection(
    name: 'Service Fees',
    description: 'Monthly service fee payments'
);

$response = $nexgen->createCollection($createCollection);

if ($response->isSuccess()) {
    $data = $response->getData();
    $collectionCode = $data['code'] ?? null;
}
```

**Name & description:** Required; 5–50 characters; only letters, digits, hyphens, and spaces (`^[A-Za-z0-9- ]+$`).

---

### getCollectionList

Retrieve a list of all collections for your account.

**Signature:**

```php
public function getCollectionList()
```

**Example:**

```php
$response = $nexgen->getCollectionList();

if ($response->isSuccess()) {
    $collections = $response->getData();
}
```

---

### getCollection

Get detailed data for one collection by its code. If `$collectionCode` is omitted, the client uses the configured default collection code.

**Signature:**

```php
public function getCollection(?string $collectionCode = null)
```

**Example:**

```php
$response = $nexgen->getCollection('RLVC2TEA0002');
// or use default: $nexgen->getCollection(null);

if ($response->isSuccess()) {
    $collection = $response->getData();
}
```

---

### getCollectionDataBilling

Get collection data plus all billings in that collection. If `$collectionCode` is omitted, the configured default is used.

**Signature:**

```php
public function getCollectionDataBilling(?string $collectionCode = null)
```

**Example:**

```php
$response = $nexgen->getCollectionDataBilling('RLVC2TEA0002');

if ($response->isSuccess()) {
    $data = $response->getData();
    $collection = $data['collection'] ?? null;
    $billings   = $data['bill_list'] ?? [];
}
```

---

### createBilling

Create a new billing (payment request) in a collection. Returns data including `payment_url`; redirect the customer to this URL to pay via DuitNow. If `$collectionCode` is omitted, the configured default is used.

**Signature:**

```php
public function createBilling(
    NexgenCreateBilling $createBilling,
    ?string $collectionCode = null
)
```

**Example:**

```php
use Reliva\Nexgen\NexgenCreateBilling;

$billing = new NexgenCreateBilling(
    fieldName: 'John Doe',
    fieldEmail: 'john@example.com',
    fieldPhone: '60123456789',
    fieldAmount: '100.00',
    fieldPaymentDescription: 'Monthly Subscription Fee',
    fieldDueDate: '2024-12-31 23:59:59',
    fieldRedirectUrl: 'https://your-domain.com/payment-success',
    fieldCallbackUrl: 'https://your-domain.com/webhook',
    fieldExternalReferenceLabel1: 'Order ID',
    fieldExternalReferenceValue1: 'ORD-12345'
);

$response = $nexgen->createBilling($billing, 'RLVC2TEA0002');

if ($response->isSuccess()) {
    $billingData = $response->getData();
    $paymentUrl  = $billingData['payment_url'] ?? null;
    return redirect($paymentUrl);
}
```

See [Usage](usage.md) and the billing DTO for the full parameter list (optional due date, redirect/callback URLs, external references 1–4).

---

### getBillingData

Get detailed data for a single billing by billing ID and (optional) collection code. If `$collectionCode` is omitted, the configured default is used.

**Signature:**

```php
public function getBillingData(string $billingId, ?string $collectionCode = null)
```

**Example:**

```php
$billingId      = 'RLVB2UE241003AWLB4';
$collectionCode = 'RLVC2TEA0002'; // optional

$response = $nexgen->getBillingData($billingId, $collectionCode);

if ($response->isSuccess()) {
    $billing = $response->getData();
}
```

---

## Typical DuitNow flow

1. **Create a collection** (once per category) with `createCollection()`; store the returned `code`.
2. **Create a billing** with `createBilling($billing, $collectionCode)`; use the returned `payment_url` to send the customer to DuitNow.
3. **Handle webhook** at your `callbackUrl` when payment completes (see [Webhooks](webhooks.md)).
4. **Optional:** List collections with `getCollectionList()`, inspect one with `getCollection()` or `getCollectionDataBilling()`, and check a specific billing with `getBillingData()`.

All methods return a `NexgenResponse`; use `$response->isSuccess()` and `$response->getData()` (see [Usage](usage.md)#error-handling).

---

## See also

- [Installation](installation.md) — Step 1
- [Usage](usage.md) — Step 2: NexgenClient config and usage
- [DuitNow QR](duitnow-qr.md) — Alternative: QR code payment flow
- [Webhooks](webhooks.md) — Webhook handler and payload
