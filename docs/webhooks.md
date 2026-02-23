# Webhooks

When a payment is completed, Nexgen sends a webhook to your `callbackUrl`. Use this guide to handle both **collections/billings** (DuitNow link) and **QR** payment callbacks.

## Table of contents

| # | Document | Description |
|---|----------|-------------|
| 1 | [**Installation**](installation.md) | Install and configure the package |
| 2 | [**Usage**](usage.md) | NexgenClient & NexgenQRClient setup and usage |
| 3 | [**DuitNow**](duitnow.md) | Collections & billings (link-based payments) |
| 4 | [**DuitNow QR**](duitnow-qr.md) | Terminals & dynamic QR payments |
| 5 | [**Webhooks**](webhooks.md) *(you are here)* | Handling payment callbacks |

## Handler example

```php
// routes/web.php or routes/api.php
Route::post('/payment/webhook', function (Request $request) {
    $billingCode = $request->input('code');
    $status = $request->input('status'); // e.g. 'paid', 'pending', 'failed'
    $amount = $request->input('amount');
    $paymentDescription = $request->input('payment_description');
    $dueDate = $request->input('due_date');

    $payerName = $request->input('payer_detail_name');
    $payerEmail = $request->input('payer_detail_email');
    $payerPhone = $request->input('payer_detail_phone');

    $externalRef1 = [
        'label' => $request->input('external_reference_label_1'),
        'value' => $request->input('external_reference_value_1'),
    ];
    // ... external_reference_2, _3, _4 similarly

    $paymentMethodAccepted = $request->input('payment_method_accepted');
    $paymentMethodDetail = $request->input('payment_method_detail');

    if ($paymentMethodDetail) {
        $transactionDate = $paymentMethodDetail['transaction_date'] ?? null;
        $transactionId = $paymentMethodDetail['transaction_id'] ?? null;
        $referenceId = $paymentMethodDetail['reference_id'] ?? null;
        $customerBank = $paymentMethodDetail['customer_bank'] ?? null;
        $customerBankType = $paymentMethodDetail['customer_bank_type'] ?? null;
    }

    // Update your database, send notifications, etc.
    return response()->json(['status' => 'success']);
})->name('payment.webhook');
```

## Payload formats

The webhook payload **differs** by flow: **DuitNow (collections/billings)** includes payer and payment-method details; **DuitNow QR** uses a simpler payload and may include `soundbox_response` when paid.

### DuitNow (collections/billings)

```json
{
  "code": "RLVB2UE241003AWLB4",
  "amount": "100.00",
  "payment_description": "Monthly Subscription Fee",
  "due_date": "31-12-2025 23:59:59",
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

**Notes:**

- `payment_method_detail` structure **varies** by payment method. Always check for field existence before accessing.
- Verify webhook signature if Nexgen provides it.

### DuitNow QR

QR payment webhooks use a simpler payload. `soundbox_response` is present only when `status` is `"paid"`; otherwise it is `null`.

```json
{
  "code": "RLVQXBP250801A0001",
  "amount": "1.00",
  "status": "paid",
  "payment_description": "Terminal QR Payment 3",
  "due_date": "01-08-2025 01:00:00",
  "external_reference_label_1": null,
  "external_reference_value_1": null,
  "external_reference_label_2": null,
  "external_reference_value_2": null,
  "callback_url": "https://webhook.site/6246a18e-2028-42ff-91c9-4e34c2d3ee29",
  "soundbox_response": "Your recevied payment 1.00 has been successfully processed."
}
```

**Notes:**

- **`soundbox_response`** — Only present when `status` is `"paid"`; otherwise `null`. Contains the message played/displayed by the soundbox.
- QR webhooks do **not** include `payer_detail_*`, `payment_method_accepted`, or `payment_method_detail`. Use `code`, `status`, `amount`, and your external references to reconcile.

## See also

- [DuitNow (collections/billings)](duitnow.md) — Where to set `callbackUrl` for billings
- [DuitNow QR](duitnow-qr.md) — Where to set `callbackUrl` for QR payments
- [Usage](usage.md) — Default callback URL configuration
