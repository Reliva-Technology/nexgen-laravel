# Nexgen Payment Gateway Integration for Laravel

A Laravel package for seamless integration with the Nexgen Payment Gateway API. This package provides a simple and intuitive interface to create collections, manage billings, and handle payment transactions.

## Features

- 🚀 **Easy Integration** - Simple setup and configuration
- 📦 **Collection Management** - Create and manage payment collections
- 💳 **Billing Management** - Create and track payment bills
- 📱 **QR Code Payments** - Generate dynamic QR codes for payments via terminals
- 🏪 **Terminal Management** - Create and manage payment terminals
- 🔄 **Environment Support** - Sandbox, Production, and Custom environments
- ✅ **Type Safety** - Built with PHP 8.1+ type hints
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

```text
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

# QR Code Payment Configuration
NEXGEN_QR_ENVIRONMENT=production
NEXGEN_QR_PROD_API_KEY=your_qr_production_api_key_here
NEXGEN_QR_PROD_API_SECRET=your_qr_production_api_secret_here
NEXGEN_QR_TERMINAL_CODE=your_terminal_code_here
NEXGEN_QR_CALLBACK_URL=https://your-domain.com/qr-callback
NEXGEN_QR_CUSTOM_ENDPOINT=https://custom-qr-endpoint.com
```

**Note:** The package automatically selects the appropriate API keys based on the `NEXGEN_ENVIRONMENT` setting:
- When `NEXGEN_ENVIRONMENT=sandbox`, it uses `NEXGEN_SANDBOX_API_KEY` and `NEXGEN_SANDBOX_API_SECRET`
- When `NEXGEN_ENVIRONMENT=production` or `custom`, it uses `NEXGEN_PROD_API_KEY` and `NEXGEN_PROD_API_SECRET`

For QR API, the package uses the QR-specific keys (`NEXGEN_QR_PROD_API_KEY` and `NEXGEN_QR_PROD_API_SECRET`) for both production and custom environments.

### Configuration Options

| Variable | Description | Required | Default |
|----------|-------------|----------|---------|
| `NEXGEN_ENVIRONMENT` | Environment: `sandbox`, `production`, or `custom` | Yes | `sandbox` |
| `NEXGEN_PROD_API_KEY` | Your Nexgen Production API key | Yes* | - |
| `NEXGEN_PROD_API_SECRET` | Your Nexgen Production API secret | Yes* | - |
| `NEXGEN_SANDBOX_API_KEY` | Your Nexgen Sandbox API key | Yes* | - |
| `NEXGEN_SANDBOX_API_SECRET` | Your Nexgen Sandbox API secret | Yes* | - |
| `NEXGEN_COLLECTION_CODE` | Default collection code | No | - |
| `NEXGEN_CALLBACK_URL` | Default callback URL for webhooks | No | - |
| `NEXGEN_REDIRECT_URL` | Default redirect URL after payment | No | - |
| `NEXGEN_CUSTOM_ENDPOINT` | Custom API endpoint (only for `custom` environment) | No | - |
| `NEXGEN_QR_ENVIRONMENT` | QR API environment: `production` or `custom` | No | `production` |
| `NEXGEN_QR_PROD_API_KEY` | Your Nexgen QR Production API key | Yes* | - |
| `NEXGEN_QR_PROD_API_SECRET` | Your Nexgen QR Production API secret | Yes* | - |
| `NEXGEN_QR_TERMINAL_CODE` | Default terminal code for QR payments | No | - |
| `NEXGEN_QR_CALLBACK_URL` | Default callback URL for QR payment webhooks | No | - |
| `NEXGEN_QR_CUSTOM_ENDPOINT` | Custom QR API endpoint (only for `custom` QR environment) | No | - |

\* Required based on environment: Sandbox keys required when using `sandbox` environment, Production keys required when using `production` or `custom` environment.

**Important:** The package automatically validates that required environment variables are set when you instantiate a client. If required keys are missing for the selected environment, an `InvalidArgumentException` will be thrown with a clear error message indicating which variables need to be set.

## Usage

### Basic Usage

The package automatically registers a service container binding. You can access the Nexgen client using the `nexgen` service or via dependency injection:

```php
use Reliva\Nexgen\NexgenClient;

// Initialize the NexgenClient instance
// API keys are automatically selected based on NEXGEN_ENVIRONMENT
protected NexgenClient $client;

    public function __construct()
    {
        $this->client = new NexgenClient();
    }
```

**Note:** When you don't provide `apiKey` and `apiSecret` parameters, the package automatically selects the appropriate keys from your configuration based on the `NEXGEN_ENVIRONMENT` setting.

**Validation:** The client validates that required environment variables are set when instantiated. If validation fails, an `InvalidArgumentException` will be thrown. See the [Error Handling](#error-handling) section for details.

### Manual Instantiation

You can also create a client instance manually. If you omit `apiKey` and `apiSecret`, the package will automatically use the environment-appropriate keys from your configuration:

```php
use Reliva\Nexgen\NexgenClient;

// Automatic key selection based on environment
$nexgen = new NexgenClient(
    environment: 'production', // 'sandbox', 'production', or 'custom'
    collectionCode: 'your_collection_code',
    callbackUrl: 'https://your-domain.com/callback',
    redirectUrl: 'https://your-domain.com/redirect'
);

// Or explicitly provide keys to override config
$nexgen = new NexgenClient(
    apiKey: 'your_api_key',
    apiSecret: 'your_api_secret',
    environment: 'production',
    collectionCode: 'your_collection_code',
    callbackUrl: 'https://your-domain.com/callback',
    redirectUrl: 'https://your-domain.com/redirect'
);
```

### QR Client Usage

For QR code payments, use the `NexgenQRClient`:

```php
use Reliva\Nexgen\NexgenQRClient;

// Initialize the NexgenQRClient instance
// QR API keys are automatically selected based on NEXGEN_QR_ENVIRONMENT
protected NexgenQRClient $client;

public function __construct()
{
    $this->client = new NexgenQRClient();
}

// Or manual instantiation
// Automatic key selection based on QR environment
$nexgenQR = new NexgenQRClient(
    environment: 'production', // 'production' or 'custom'
    terminalCode: 'your_terminal_code',
    callbackUrl: 'https://your-domain.com/qr-callback'
);

// Or explicitly provide QR keys to override config
$nexgenQR = new NexgenQRClient(
    apiKey: 'your_qr_api_key',
    apiSecret: 'your_qr_api_secret',
    environment: 'production',
    terminalCode: 'your_terminal_code',
    callbackUrl: 'https://your-domain.com/qr-callback'
);
```

**Note:** The QR client uses separate QR-specific API keys (`NEXGEN_QR_PROD_API_KEY` and `NEXGEN_QR_PROD_API_SECRET`) which are different from the regular API keys.

**Validation:** The QR client validates that required environment variables are set when instantiated. If validation fails, an `InvalidArgumentException` will be thrown. See the [Error Handling](#error-handling) section for details.

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
| `fieldExternalReferenceValue1` | string | No | Custom reference value #1 (required if `fieldExternalReferenceLabel1`) provided |
| `fieldExternalReferenceValue2` | string | No | Custom reference value #2 (required if `fieldExternalReferenceLabel2`) provided |
| `fieldExternalReferenceValue3` | string | No | Custom reference value #3 (required if `fieldExternalReferenceLabel3`) provided |
| `fieldExternalReferenceValue4` | string | No | Custom reference value #4 (required if `fieldExternalReferenceLabel4`) provided |

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

## QR Code Payments

The `NexgenQRClient` provides functionality for generating dynamic QR codes for payments through terminals. This is useful for point-of-sale systems, retail stores, or any scenario where you need to generate QR codes for customers to scan and pay.

### Terminals

Terminals are physical or virtual payment points where QR codes can be generated. Each terminal can have multiple QR code transactions.

#### Create a Terminal

```php
use Reliva\Nexgen\NexgenQRClient;
use Reliva\Nexgen\NexgenCreateTerminal;

$nexgenQR = app('nexgen-qr');

$createTerminal = new NexgenCreateTerminal(
    name: 'Store Counter 1',
    description: 'Main checkout counter'
);

$response = $nexgenQR->createTerminal($createTerminal);

if ($response->isSuccess()) {
    $terminalData = $response->getData();
    $terminalCode = $terminalData['code'] ?? null;
    // Store terminal code for future use
}
```

**Terminal Name & Description Requirements:**
- Required (cannot be empty)
- Minimum length: 5 characters
- Maximum length: 50 characters
- Only letters (A-Z, a-z), digits (0-9), hyphens (-), and spaces are allowed

#### Get Terminal List

Retrieve all terminals associated with your account:

```php
$response = $nexgenQR->getTerminalList();

if ($response->isSuccess()) {
    $terminals = $response->getData();
    // Process terminals array
}
```

#### Get Terminal Data

Retrieve detailed information for a specific terminal:

```php
$terminalCode = 'RLVT2TEA0001';

$response = $nexgenQR->getTerminal($terminalCode);

if ($response->isSuccess()) {
    $terminal = $response->getData();
    // Process terminal data
}
```

If no `$terminalCode` is provided, it will use the default terminal code from your configuration.

#### Get Terminal Data with Billing List

Retrieve terminal data along with all associated QR code transactions:

```php
$terminalCode = 'RLVT2TEA0001';

$response = $nexgenQR->getTerminalDataBilling($terminalCode);

if ($response->isSuccess()) {
    $data = $response->getData();
    $terminal = $data['terminal'] ?? null;
    $billings = $data['bill_list'] ?? [];
}
```

### Dynamic QR Codes

Dynamic QR codes are payment QR codes generated on-demand for specific transactions. Customers scan the QR code to complete payment.

#### Create a Dynamic QR Code

```php
use Reliva\Nexgen\NexgenQRClient;
use Reliva\Nexgen\NexgenCreateDynamicQR;

$nexgenQR = app('nexgen-qr');

$createDynamicQR = new NexgenCreateDynamicQR(
    fieldAmount: '50.00',
    fieldPaymentDescription: 'Purchase at Store Counter 1',
    fieldCallbackUrl: 'https://your-domain.com/qr-webhook',
    fieldExternalReferenceLabel1: 'Order ID',
    fieldExternalReferenceValue1: 'ORD-12345',
    fieldExternalReferenceLabel2: 'Customer ID',
    fieldExternalReferenceValue2: 'CUST-789'
);

$terminalCode = 'RLVT2TEA0001'; // Optional, uses config default if not provided

$response = $nexgenQR->createDynamicQR($createDynamicQR, $terminalCode);

if ($response->isSuccess()) {
    $qrData = $response->getData();
    $qrCode = $qrData['code'] ?? null;
    $qrImageUrl = $qrData['qr_image_url'] ?? null;
    $qrString = $qrData['qr_string'] ?? null;
    
    // Display QR code to customer
    // You can use $qrImageUrl to display the QR code image
    // Or use $qrString to generate your own QR code
}
```

**Dynamic QR Parameters:**

| Parameter | Type | Required | Description |
|-----------|------|----------|-------------|
| `fieldAmount` | string | Yes | Payment amount (e.g., "50.00") |
| `fieldPaymentDescription` | string | Yes | Description of the payment |
| `fieldCallbackUrl` | string | No | Webhook callback URL (uses config default if not provided) |
| `fieldExternalReferenceLabel1` | string | No | Custom reference label #1 |
| `fieldExternalReferenceLabel2` | string | No | Custom reference label #2 |
| `fieldExternalReferenceValue1` | string | No | Custom reference value #1 (required if `fieldExternalReferenceLabel1` provided) |
| `fieldExternalReferenceValue2` | string | No | Custom reference value #2 (required if `fieldExternalReferenceLabel2` provided) |

#### Get QR Code Data

Retrieve detailed information for a specific QR code transaction:

```php
$qrCode = 'RLVQ2UE241003AWLB4';
$terminalCode = 'RLVT2TEA0001'; // Optional

$response = $nexgenQR->getQRData($qrCode, $terminalCode);

if ($response->isSuccess()) {
    $qrData = $response->getData();
    $status = $qrData['status'] ?? null; // e.g., 'paid', 'pending', 'expired'
    // Process QR code data
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

### NexgenClient Environments

The `NexgenClient` supports three environments. API keys are automatically selected based on the environment when not explicitly provided:

#### Sandbox (Default)

```php
// Automatic key selection - uses NEXGEN_SANDBOX_API_KEY and NEXGEN_SANDBOX_API_SECRET
$nexgen = new NexgenClient(
    environment: 'sandbox'
);
```

**Endpoint:** `https://dash-nexgen-stg.reliva.com.my`  
**API Keys Used:** `NEXGEN_SANDBOX_API_KEY` and `NEXGEN_SANDBOX_API_SECRET`

#### Production

```php
// Automatic key selection - uses NEXGEN_PROD_API_KEY and NEXGEN_PROD_API_SECRET
$nexgen = new NexgenClient(
    environment: 'production'
);
```

**Endpoint:** `https://dash-nexgen.reliva.com.my`  
**API Keys Used:** `NEXGEN_PROD_API_KEY` and `NEXGEN_PROD_API_SECRET`

#### Custom

```php
// Automatic key selection - uses NEXGEN_PROD_API_KEY and NEXGEN_PROD_API_SECRET
$nexgen = new NexgenClient(
    environment: 'custom'
);
```

**Endpoint:** Uses `NEXGEN_CUSTOM_ENDPOINT` from your configuration.  
**API Keys Used:** `NEXGEN_PROD_API_KEY` and `NEXGEN_PROD_API_SECRET`

### NexgenQRClient Environments

The `NexgenQRClient` supports two environments (no sandbox support). QR API keys are automatically selected based on the environment when not explicitly provided:

#### Production (Default)

```php
// Automatic key selection - uses NEXGEN_QR_PROD_API_KEY and NEXGEN_QR_PROD_API_SECRET
$nexgenQR = new NexgenQRClient(
    environment: 'production'
);
```

**Endpoint:** `https://dash-nexgen.reliva.com.my`  
**API Keys Used:** `NEXGEN_QR_PROD_API_KEY` and `NEXGEN_QR_PROD_API_SECRET`

#### Custom

```php
// Automatic key selection - uses NEXGEN_QR_PROD_API_KEY and NEXGEN_QR_PROD_API_SECRET
$nexgenQR = new NexgenQRClient(
    environment: 'custom'
);
```

**Endpoint:** Uses `NEXGEN_QR_CUSTOM_ENDPOINT` from your configuration.  
**API Keys Used:** `NEXGEN_QR_PROD_API_KEY` and `NEXGEN_QR_PROD_API_SECRET`

## Complete Example

Here's a complete example of creating a collection and billing:

```php
use Reliva\Nexgen\NexgenClient;
use Reliva\Nexgen\NexgenCreateCollection;
use Reliva\Nexgen\NexgenCreateBilling;

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

### Configuration Validation Errors

When instantiating `NexgenClient` or `NexgenQRClient`, the package validates that required environment variables are set. If validation fails, an `InvalidArgumentException` is thrown:

```php
use Reliva\Nexgen\NexgenClient;
use InvalidArgumentException;

try {
    $nexgen = new NexgenClient();
} catch (InvalidArgumentException $e) {
    // Handle missing configuration
    // Example error messages:
    // - "NexgenClient requires NEXGEN_ENVIRONMENT to be set..."
    // - "NexgenClient requires the following environment variables to be set when using 'sandbox' environment: NEXGEN_SANDBOX_API_KEY, NEXGEN_SANDBOX_API_SECRET..."
    
    \Log::error('Nexgen Configuration Error', [
        'message' => $e->getMessage()
    ]);
    
    return back()->withErrors([
        'configuration' => $e->getMessage()
    ]);
}
```

**Common Validation Errors:**

- **Missing Environment:** `NEXGEN_ENVIRONMENT` or `NEXGEN_QR_ENVIRONMENT` not set
- **Missing Sandbox Keys:** When using `sandbox` environment, `NEXGEN_SANDBOX_API_KEY` and/or `NEXGEN_SANDBOX_API_SECRET` are missing
- **Missing Production Keys:** When using `production` or `custom` environment, `NEXGEN_PROD_API_KEY` and/or `NEXGEN_PROD_API_SECRET` are missing
- **Missing QR Keys:** When using `NexgenQRClient`, `NEXGEN_QR_PROD_API_KEY` and/or `NEXGEN_QR_PROD_API_SECRET` are missing

### API Response Errors

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

### NexgenClient Helper Methods

| Method | Description | Return Type |
|--------|-------------|-------------|
| `getEndpoint()` | Get current API endpoint | `string` |
| `getEnvironment()` | Get current environment | `string` |
| `isSandbox()` | Check if in sandbox mode | `bool` |
| `getApiKey()` | Get API key | `string` |
| `getApiSecret()` | Get API secret | `string` |
| `getCollectionCode()` | Get collection code | `string` |
| `getConfig()` | Get all configuration | `array` |

### NexgenQRClient Methods

| Method | Description | Parameters |
|--------|-------------|------------|
| `createTerminal()` | Create a new terminal | `NexgenCreateTerminal $createTerminal` |
| `getTerminalList()` | Get all terminals | - |
| `getTerminal()` | Get terminal data | `?string $terminalCode = null` |
| `getTerminalDataBilling()` | Get terminal with billing list | `?string $terminalCode = null` |
| `createDynamicQR()` | Create a dynamic QR code | `NexgenCreateDynamicQR $createDynamicQR, ?string $terminalCode = null` |
| `getQRData()` | Get QR code data | `string $qr_code, ?string $terminalCode = null` |

### NexgenQRClient Helper Methods

| Method | Description | Return Type |
|--------|-------------|-------------|
| `getEndpoint()` | Get current API endpoint | `string` |
| `getEnvironment()` | Get current environment | `string` |
| `getApiKey()` | Get API key | `string` |
| `getApiSecret()` | Get API secret | `string` |
| `getTerminalCode()` | Get terminal code | `string` |
| `getCallbackUrl()` | Get callback URL | `string` |
| `getConfig()` | Get all configuration | `array` |


## License

This package is open-sourced software licensed under the [MIT license](LICENSE).

## Changelog

Please see the [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

