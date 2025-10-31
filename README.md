<div align="center">
  <img src="https://github.com/user-attachments/assets/bdf487ad-9d8b-424b-b432-9a05ddcc1361" alt="Clover Laravel Payments" width="1536" height="600"/>
  <h1>💳 Clover-Laravel Payments Plugin</h1>
  <p>Seamlessly integrate <b>Clover</b> payments into your Laravel application for secure, tokenized checkout.</p>
</div>

---

# 🚀 Overview

Integrate **Clover** as a secure payment method in your **Laravel online store** to start accepting payments with ease and reliability.

---

## ✨ Key Features

- 🔐 **OAuth 2.0 Authorization Code Flow** for secure Clover authentication  
- 💳 **Tokenized Card Payments** — create and process payment tokens securely  
- 💾 **Automatic Transaction Logging** in the database  
- ⚙️ **Easy Configuration** via `config/clover.php`  
- 🚀 **Ready-to-use Routes & Controllers** for seamless integration  
- 🧱 Built on `GuzzleHttp\Client` for robust API communication  
- ✅ **Compatible with Laravel 9–12** and **PHP ≥ 8.1**  
- 🌍 Supports **Sandbox** and **Production** environments  
- 🧾 Built-in **Logging System** for tracking API requests & responses  

---
---

## 🪜 Clover Payments Setup Guide

1. **Install the Plugin** — Download and install **Clover-Laravel Payments** via Composer.  
2. **Connect Your Clover Account** — Log in to your **Clover Merchant Dashboard** and authorize the plugin.  
3. **Configure Payment Settings** — Match the same payment tenders (e.g., card networks) in both **Clover** and **Laravel** for smooth transaction processing.  
4. **Activate as Payment Method** — Enable **Clover Payment** in your Laravel Online Shop to start accepting payments securely.  

---

## ⚙️ Installation

### 1️⃣ Install the Plugin

```bash
composer require supravatm/clover-laravel-payments-plugin
```

### 2️⃣ Publish Configuration & Run Migration

```bash
php artisan vendor:publish --provider="Supravatm\CloverPayment\CloverPaymentServiceProvider"
php artisan migrate
```

### 3️⃣ Configure Environment

Add the following to your `.env`:

```env
CLOVER_ENV=sandbox
CLOVER_MERCHANT_ID=your_merchant_id
CLOVER_ACCESS_TOKEN=your_private_token
CLOVER_PUBLIC_KEY=your_public_token
CLOVER_API_URL=https://sandbox.dev.clover.com/v3/merchants
CLOVER_TOKEN_URL=https://token-sandbox.dev.clover.com/v1/tokens
CLOVER_TENDER_ID=your_tender_id
CLOVER_OAUTH_URL=https://sandbox.dev.clover.com/oauth/token
CLOVER_REDIRECT_URL=http://your-app.test/oauth/callback
CLOVER_APP_ID=your_app_id
CLOVER_APP_SECRET=your_app_secret
```

---

## 🗃️ Database Schema: `clover_payment_transactions`

| Field | Type | Description |
|-------|------|-------------|
| `id` | BIGINT | Primary key |
| `order_id` | VARCHAR(255) | Internal order reference |
| `transaction_id` | VARCHAR(255) | Clover transaction ID |
| `amount` | DECIMAL(10,2) | Transaction amount |
| `status` | VARCHAR(255) | Transaction status |
| `response_payload` | JSON | Clover API response |
| `created_at` | TIMESTAMP | Record creation time |
| `updated_at` | TIMESTAMP | Last updated time |

---

## 💳 API Usage

### Create Token

**POST** `/api/create-token`
```json
{
  "card": {
    "number": "4242424242424242",
    "exp_month": 12,
    "exp_year": 2026,
    "cvv": "123"
  }
}
```

**Response**
```json
{
  "token": "clv_tok_123456",
  "status": "success"
}
```

### Make Payment

**POST** `/api/make-payment`
```json
{
  "amount": 5000,
  "currency": "usd",
  "token": "clv_tok_123456",
  "order_id": "ORD001"
}
```

**Response**
```json
{
  "status": "success",
  "payment_id": "PAY_987654321",
  "transaction_id": "TXN_20251031ABC"
}
```

---

## 🧪 Testing in Browser


Visit the checkout demo page:

```
http://localhost:8000/checkout
```

<p align="center">
  <img src="https://github.com/user-attachments/assets/3068cc04-0d38-4c35-aefe-7f598f0e7313" alt="Clover Laravel Payments Checkout Page" width="333" height="444">
</p>

---

## 🧾 Logging

All Clover API requests and responses are logged at: `storage/logs/clover-*.log`

---

## 🪪 License

Released under the [MIT License](LICENSE).