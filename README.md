# Nexgen Payment Gateway Integration for Laravel

A Laravel package for seamless integration with the Nexgen Payment Gateway API. This package provides a simple and intuitive interface to create collections, manage billings, and handle payment transactions.

## Features

- 🚀 **Easy Integration** - Simple setup and configuration
- 📦 **Collection Management** - Create and manage payment collections
- 💳 **Billing Management** - Create and track payment bills
- 🔄 **Environment Support** - Sandbox, Production, and Custom environments
- ✅ **Type Safety** - Built with PHP 8.1+ enums and type hints
- 🛡️ **Secure** - API key and secret authentication

## Requirements

- PHP >= 8.1
- Laravel >= 8.0
- Composer

## Installation

Install the package via Composer:

```bash
composer require reliva/nexgen
```

## Configuration

### Publish Configuration

Publish the configuration file to your `config` directory:

```bash
php artisan vendor:publish --tag=nexgen-config
```

This will create a `config/nexgen.php` file in your Laravel application.

### Environment Variables

Add the following environment variables to your `.env` file:

```env
NEXGEN_ENVIRONMENT=sandbox
NEXGEN_API_KEY=your_api_key_here
NEXGEN_API_SECRET=your_api_secret_here
NEXGEN_COLLECTION_CODE=your_collection_code_here
NEXGEN_CALLBACK_URL=https://your-domain.com/callback
NEXGEN_REDIRECT_URL=https://your-domain.com/redirect
NEXGEN_CUSTOM_ENDPOINT=https://custom-endpoint.com
```

### Configuration Options

| Variable | Description | Required | Default |
|----------|-------------|----------|---------|
| `NEXGEN_ENVIRONMENT` | Environment: `sandbox`, `production`, or `custom` | Yes | `sandbox` |
| `NEXGEN_API_KEY` | Your Nexgen API key | Yes | - |
| `NEXGEN_API_SECRET` | Your Nexgen API secret | Yes | - |
| `NEXGEN_COLLECTION_CODE` | Default collection code | No | - |
| `NEXGEN_CALLBACK_URL` | Default callback URL for webhooks | No | - |
| `NEXGEN_REDIRECT_URL` | Default redirect URL after payment | No | - |
| `NEXGEN_CUSTOM_ENDPOINT` | Custom API endpoint (only for `custom` environment) | No | - |

## Usage

### Basic Usage

The package automatically registers a service container binding. You can access the Nexgen client using the `nexgen` service or via dependency injection:

```php
use Reliva\Nexgen\NexgenClient;

// Via service container
$nexgen = app('nexgen');

// Or via dependency injection
public function __construct(NexgenClient $nexgen)
{
    $this->nexgen = $nexgen;
}
```

### Manual Instantiation

You can also create a client instance manually:

```php
use Reliva\Nexgen\NexgenClient;
use Reliva\Nexgen\Enum\NexgenEnvironment;

$nexgen = new NexgenClient(
    apiKey: 'your_api_key',
    apiSecret: 'your_api_secret',
    environment: NexgenEnvironment::PRODUCTION,
    collectionCode: 'your_collection_code',
    callbackUrl: 'https://your-domain.com/callback',
    redirectUrl: 'https://your-domain.com/redirect'
);
```

## Collections

Collections are groups of related bills that help organize and manage different types of payments. For example, you can create a 'Service Fees' collection for bills related to service charges.

### Create a Collection

```php
use Reliva\Nexgen\NexgenCreateCollection;

$createCollection = new NexgenCreateCollection(
    name: 'Service Fees',
    description: 'Monthly service fee payments'
);

$response = $nexgen->createCollection($createCollection);

if ($response->isSuccess()) {
    $collectionData = $response->getData();
    // Handle successful collection creation
    $collectionCode = $collectionData['code'] ?? null;
} else {
    // Handle error
    $error = $response->getData();
}
```

**Collection Name & Description Requirements:**
- Required (cannot be empty)
- Minimum length: 5 characters
- Maximum length: 50 characters
- Only letters (A-Z, a-z), digits (0-9), hyphens (-), and spaces are allowed

### Get Collection List

Retrieve all collections associated with your account:

```php
$response = $nexgen->getCollectionList();

if ($response->isSuccess()) {
    $collections = $response->getData();
    // Process collections array
}
```

### Get Collection Data

Retrieve detailed information for a specific collection:

```php
$collectionCode = 'RLVC2TEA0002';

$response = $nexgen->getCollection($collectionCode);

if ($response->isSuccess()) {
    $collection = $response->getData();
    // Process collection data
}
```

If no `$collectionCode` is provided, it will use the default collection code from your configuration.

### Get Collection Data with Billing List

Retrieve collection data along with all associated bills:

```php
$collectionCode = 'RLVC2TEA0002';

$response = $nexgen->getCollectionDataBilling($collectionCode);

if ($response->isSuccess()) {
    $data = $response->getData();
    $collection = $data['collection'] ?? null;
    $billings = $data['bill_list'] ?? [];
}
```

## Billings

Billings are payment requests created for customers. Each billing must be associated with a collection.

### Create a Billing

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

$collectionCode = 'RLVC2TEA0002'; // Optional, uses config default if not provided

$response = $nexgen->createBilling($billing, $collectionCode);

if ($response->isSuccess()) {
    $billingData = $response->getData();
    $billingCode = $billingData['code'] ?? null;
    $paymentUrl = $billingData['payment_url'] ?? null;
    
    // Redirect user to payment URL
    return redirect($paymentUrl);
}
```

**Billing Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `fieldName` | string | Yes | Payer's name |
| `fieldEmail` | string | Yes | Payer's email address |
| `fieldPhone` | string | Yes | Payer's phone number |
| `fieldAmount` | string | Yes | Payment amount (e.g., "100.00") |
| `fieldPaymentDescription` | string | Yes | Description of the payment |
| `fieldDueDate` | string | No | Due date in format: `YYYY-MM-DD HH:MM:SS` |
| `fieldRedirectUrl` | string | No | URL to redirect after payment (uses config default if not provided) |
| `fieldCallbackUrl` | string | No | Webhook callback URL (uses config default if not provided) |
| `fieldExternalReferenceLabel1` | string | No | Custom reference label #1 |
| `fieldExternalReferenceLabel2` | string | No | Custom reference label #2 |
| `fieldExternalReferenceLabel3` | string | No | Custom reference label #3 |
| `fieldExternalReferenceLabel4` | string | No | Custom reference label #4 |
| `fieldExternalReferenceValue1` | string | No | Custom reference value #1 |
| `fieldExternalReferenceValue2` | string | No | Custom reference value #2 |
| `fieldExternalReferenceValue3` | string | No | Custom reference value #3 |
| `fieldExternalReferenceValue4` | string | No | Custom reference value #4 |

### Get Billing Data

Retrieve detailed information for a specific billing:

```php
$billingId = 'RLVB2UE241003AWLB4';
$collectionCode = 'RLVC2TEA0002'; // Optional

$response = $nexgen->getBillingData($billingId, $collectionCode);

if ($response->isSuccess()) {
    $billing = $response->getData();
    // Process billing data
}
```

## Response Handling

All API methods return a `NexgenResponse` object with the following methods:

```php
$response = $nexgen->createBilling($billing);

// Check if request was successful
if ($response->isSuccess()) {
    // Get response data
    $data = $response->getData();
    
    // Convert to array
    $array = $response->toArray();
    // Returns: ['success' => true, 'data' => [...]]
} else {
    // Handle error
    $errorData = $response->getData();
    $errorMessage = $errorData['error'] ?? 'An error occurred';
}
```

## Environments

The package supports three environments:

### Sandbox (Default)

```php
use Reliva\Nexgen\Enum\NexgenEnvironment;

$nexgen = new NexgenClient(
    apiKey: 'your_api_key',
    apiSecret: 'your_api_secret',
    environment: NexgenEnvironment::SANDBOX
);
```

**Endpoint:** `https://dash-nexgen-stg.reliva.com.my`

### Production

```php
$nexgen = new NexgenClient(
    apiKey: 'your_api_key',
    apiSecret: 'your_api_secret',
    environment: NexgenEnvironment::PRODUCTION
);
```

**Endpoint:** `https://dash-nexgen.reliva.com.my`

### Custom

```php
$nexgen = new NexgenClient(
    apiKey: 'your_api_key',
    apiSecret: 'your_api_secret',
    environment: NexgenEnvironment::CUSTOM
);
```

**Endpoint:** Uses `NEXGEN_CUSTOM_ENDPOINT` from your configuration.

## Complete Example

Here's a complete example of creating a collection and billing:

```php
use Reliva\Nexgen\NexgenClient;
use Reliva\Nexgen\NexgenCreateCollection;
use Reliva\Nexgen\NexgenCreateBilling;
use Reliva\Nexgen\Enum\NexgenEnvironment;

// Initialize client
$nexgen = app('nexgen');

// Create a collection
$collection = new NexgenCreateCollection(
    name: 'Subscription Payments',
    description: 'Monthly subscription fee collection'
);

$collectionResponse = $nexgen->createCollection($collection);

if (!$collectionResponse->isSuccess()) {
    return back()->withErrors(['error' => 'Failed to create collection']);
}

$collectionData = $collectionResponse->getData();
$collectionCode = $collectionData['code'];

// Create a billing
$billing = new NexgenCreateBilling(
    fieldName: 'Jane Smith',
    fieldEmail: 'jane@example.com',
    fieldPhone: '60123456789',
    fieldAmount: '99.99',
    fieldPaymentDescription: 'Monthly Subscription - January 2024',
    fieldDueDate: '2024-01-31 23:59:59',
    fieldRedirectUrl: route('payment.success'),
    fieldCallbackUrl: route('payment.webhook'),
    fieldExternalReferenceLabel1: 'User ID',
    fieldExternalReferenceValue1: '12345'
);

$billingResponse = $nexgen->createBilling($billing, $collectionCode);

if ($billingResponse->isSuccess()) {
    $billingData = $billingResponse->getData();
    $paymentUrl = $billingData['payment_url'];
    
    // Redirect to payment page
    return redirect($paymentUrl);
} else {
    return back()->withErrors(['error' => 'Failed to create billing']);
}
```

## Webhook Handling

When a payment is completed, Nexgen will send a webhook to your `callbackUrl`. Here's an example webhook handler:

```php
// routes/web.php or routes/api.php
Route::post('/payment/webhook', function (Request $request) {
    // Verify webhook signature if provided by Nexgen
    // Process payment status update
    
    $billingCode = $request->input('code');
    $status = $request->input('status'); // e.g., 'paid', 'pending', 'failed'
    $amount = $request->input('amount');
    $paymentDescription = $request->input('payment_description');
    $dueDate = $request->input('due_date');
    
    // Payer details
    $payerName = $request->input('payer_detail_name');
    $payerEmail = $request->input('payer_detail_email');
    $payerPhone = $request->input('payer_detail_phone');
    
    // External references
    $externalRef1 = [
        'label' => $request->input('external_reference_label_1'),
        'value' => $request->input('external_reference_value_1'),
    ];
    $externalRef2 = [
        'label' => $request->input('external_reference_label_2'),
        'value' => $request->input('external_reference_value_2'),
    ];
    // ... handle external_reference_3 and external_reference_4 similarly
    
    // Payment method information
    $paymentMethodAccepted = $request->input('payment_method_accepted');
    $paymentMethodDetail = $request->input('payment_method_detail'); // Can vary by payment method
    
    // Handle payment_method_detail based on payment method
    // Note: payment_method_detail structure varies for different payment methods
    if ($paymentMethodDetail) {
        // Common fields that may be present:
        $transactionDate = $paymentMethodDetail['transaction_date'] ?? null;
        $transactionId = $paymentMethodDetail['transaction_id'] ?? null;
        $referenceId = $paymentMethodDetail['reference_id'] ?? null;
        $customerBank = $paymentMethodDetail['customer_bank'] ?? null;
        $customerBankType = $paymentMethodDetail['customer_bank_type'] ?? null;
        
        // Additional fields may exist depending on the payment method
        // Always check for field existence before accessing
    }
    
    // Update your database, send notifications, etc.
    // Example:
    // Payment::where('billing_code', $billingCode)->update([
    //     'status' => $status,
    //     'paid_at' => $status === 'paid' ? now() : null,
    //     'transaction_id' => $transactionId ?? null,
    // ]);
    
    return response()->json(['status' => 'success']);
})->name('payment.webhook');
```

### Webhook Response Format

The webhook payload includes the following fields:

```json
{
  "code": "RLVB2UE241003AWLB4",
  "amount": "100.00",
  "payment_description": "Monthly Subscription Fee",
  "due_date": "31-12-2024 23:59:59",
  "status": "paid",
  "payer_detail_name": "John Doe",
  "payer_detail_email": "john.doe@example.com",
  "payer_detail_phone": "60123456789",
  "external_reference_label_1": "Order ID",
  "external_reference_value_1": "ORD-12345",
  "external_reference_label_2": null,
  "external_reference_value_2": null,
  "external_reference_label_3": null,
  "external_reference_value_3": null,
  "external_reference_label_4": null,
  "external_reference_value_4": null,
  "redirect_url": "https://example.com/payment/success",
  "callback_url": "https://example.com/webhook/nexgen",
  "payment_url": "https://dash-nexgen-stg.reliva.com.my/p/b/RLVB2UE241003AWLB4/1",
  "payment_method_accepted": "DuitNow Online Banking/Wallet",
  "payment_method_detail": {
    "transaction_date": "15-12-2024 14:30:00",
    "transaction_id": "20241215M001234567890000123",
    "reference_id": "REF123456789",
    "customer_bank": "BANKMYK1|Example Bank",
    "customer_bank_type": "RET"
  }
}
```

**Important Notes:**
- The `payment_method_detail` object structure **varies** depending on the payment method used
- Always check for field existence before accessing nested properties
- Additional fields may be present for specific payment methods

## Error Handling

Always check the response status before processing data:

```php
$response = $nexgen->createBilling($billing);

if ($response->isSuccess()) {
    // Process success
    $data = $response->getData();
} else {
    // Handle error
    $errorData = $response->getData();
    
    // Log error
    \Log::error('Nexgen API Error', $errorData);
    
    // Return error to user
    return back()->withErrors([
        'payment' => $errorData['error'] ?? 'Payment processing failed'
    ]);
}
```

## API Reference

### NexgenClient Methods

| Method | Description | Parameters |
|--------|-------------|------------|
| `createCollection()` | Create a new collection | `NexgenCreateCollection $createCollection` |
| `getCollectionList()` | Get all collections | - |
| `getCollection()` | Get collection data | `?string $collectionCode = null` |
| `getCollectionDataBilling()` | Get collection with billing list | `?string $collectionCode = null` |
| `createBilling()` | Create a new billing | `NexgenCreateBilling $createBilling, ?string $collectionCode = null` |
| `getBillingData()` | Get billing data | `string $billingId, ?string $collectionCode = null` |

### Helper Methods

| Method | Description | Return Type |
|--------|-------------|-------------|
| `getEndpoint()` | Get current API endpoint | `string` |
| `getEnvironment()` | Get current environment | `string` |
| `isSandbox()` | Check if in sandbox mode | `bool` |
| `getApiKey()` | Get API key | `string` |
| `getApiSecret()` | Get API secret | `string` |
| `getCollectionCode()` | Get collection code | `string` |
| `getConfig()` | Get all configuration | `array` |


## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Changelog

Please see the [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

