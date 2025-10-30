# Laravel Clover-Pay  
A Laravel package to integrate the **Clover Payment Gateway** for secure credit-card checkout, tokenization, and transaction tracking.

---

## 🧾 Description  
**Laravel Clover-Pay** is a Laravel package that simplifies connecting your application with the **Clover Payments API**.  
It provides a structured approach to handle:
- OAuth 2.0 authentication  
- Token generation  
- Payment execution  
- Transaction persistence in the database  

All HTTP communication is handled through `GuzzleHttp\Client`, ensuring reliability and simplicity.

---

## ✨ Features  
- 🔐 Clover OAuth 2.0 Authorization Code flow  
- 💳 Secure card token creation and payment processing  
- 💾 Built-in database transaction recording  
- ⚙️ Configuration via `config/clover.php`  
- 🚀 Laravel-ready routes and controllers  
- 🧱 Uses `GuzzleHttp\Client`  
- ✅ Compatible with Laravel 9–12 and PHP ≥ 8.1  

---

## ⚙️ Installation  

### 1️⃣ Install the package  
```bash
composer require supravatm/laravel-clover-pay
composer require guzzlehttp/guzzle
```

### 2️⃣ Publish the configuration file  
```bash
php artisan vendor:publish --provider="Supravatm\CloverPayment\CloverPaymentServiceProvider"
```

This will create:

```
config/clover.php
```

### 3️⃣ Update your `.env`
```env

CLOVER_ENV=sandbox
CLOVER_MERCHANT_ID=your_merchant_id
CLOVER_ACCESS_TOKEN=your_private_token
CLOVER_PUBLIC_KEY=your_public_token
CLOVER_API_URL=https://sandbox.dev.clover.com/v3/merchants // or production url
CLOVER_TOKEN_URL=https://token-sandbox.dev.clover.com/v1/tokens / or production url
CLOVER_TENDER_ID=your_tender_id
CLOVER_OAUTH_URL=https://sandbox.dev.clover.com/oauth/token
CLOVER_REDIRECT_URL=http://vitalos.local:8080/oauth/callback //callback url for oauth token
CLOVER_APP_ID=your_app_id
CLOVER_APP_SECRET=your_app_secret
```

---

## 🔑 OAuth Token Endpoint  

Clover uses **OAuth 2.0 Authorization Code Flow** to generate access tokens that expire after a short period.  
You must first obtain an **authorization code** from Clover, then exchange it for an access token.

**Reference:**  
👉 [Clover Developer Docs – Generate Expiring Tokens using V2 OAuth Flow](https://docs.clover.com/dev/docs/generate-expiring-tokens-using-v2-oauth-flow)

**Example implementation:**
```php
$response = $this->client->post($this->config['oauth_url'], [
    'headers' => [
        'Content-Type' => 'application/json',
    ],
    'json' => [
        'client_id'     => $this->config['app_id'],
        'client_secret' => $this->config['app_secret'],
        'code'          => $code,
        'grant_type'    => 'authorization_code',
        'redirect_uri'  => $this->config['redirect_url'],
    ],
]);
```

This request retrieves an **access token** from Clover, which you must include in all subsequent API calls:

```
Authorization: Bearer {access_token}
```

---

## 💳 Usage  

### Create Token  

```bash
POST /api/create-token
Content-Type: application/json

{
  "card": {
    "number": "4242424242424242",
    "exp_month": 12,
    "exp_year": 2026,
    "cvv": "123"
  }
}
```

**Response:**
```json
{
  "token": "clv_tok_123456",
  "status": "success"
}
```

---

### Make Payment  

```bash
POST /api/make-payment
Content-Type: application/json

{
  "amount": 5000,
  "currency": "usd",
  "token": "clv_tok_123456",
  "order_id": "ORD001"
}
```

**Response:**
```json
{
  "status": "success",
  "payment_id": "PAY_987654321",
  "transaction_id": "TXN_20251031ABC"
}
```

---

## 💾 Transaction Storage  

When a payment is completed, all details are stored in the `clover_payment_transactions` table.  

### Migration File  
**`database/migrations/2025_10_26_135758_create_clover_payment_transactions_table.php`**

```php
Schema::create('clover_payment_transactions', function (Blueprint $table) {
    $table->id();
    $table->string('order_id')->nullable();
    $table->string('transaction_id')->nullable();
    $table->decimal('amount', 10, 2);
    $table->string('status')->default('PENDING');
    $table->json('response_payload')->nullable();
    $table->timestamps();
});
```

### Run the migration  
```bash
php artisan migrate
```

Once migrated, every transaction response from Clover will be recorded automatically in this table.

---

## 🧠 Why This Module Was Developed  

Integrating Clover’s payment system directly requires managing:
- OAuth token exchange  
- Token-based card payments  
- Secure storage of responses  

This module abstracts all that complexity into a **plug-and-play Laravel solution**, providing:
- Clean API endpoints (`/api/create-token`, `/api/make-payment`)  
- Configurable service class for API calls  
- Automated transaction persistence  

It saves developer time while enforcing Laravel’s best practices for payment flow integration.

---

## 🧪 Testing  

Got it ✅ — here’s the updated **“Testing”** section rewritten based on your provided route file and request.

It removes the *Postman/cURL* examples and focuses on browser-based testing using the `/checkout` Blade page.

---

### 🧪 Testing

Once the package is installed and configured correctly, you can test the **Clover Payment integration** directly from your browser.

The package registers a few web routes (defined under the `web` middleware group):

```php
Route::group(['middleware' => ['web']], function () {
    Route::get('/test', [CloverCheckoutController::class, 'test']);
    Route::get('/checkout', [CloverCheckoutController::class, 'index'])->name('checkout');
    Route::get('/oauth/callback', [CloverOAuthController::class, 'handleCallback']);
});
```

### 🧭 How to Test in Browser

1. **Start your local Laravel server**

   ```bash
   php artisan serve
   ```

   (or run your project as usual in Valet, Sail, or Homestead)

2. **Visit the checkout page**
   Open your browser and navigate to:

   ```
   http://localhost:8000/checkout
   ```

3. **Clover Checkout Page**
   This page (`resources/views/vendor/clover-payment/checkout.blade.php`) provides a **sample checkout interface** that allows you to:

   * Enter a test credit card number
   * Input expiry and CVV
   * Simulate the full Clover payment flow

4. **Successful Transaction**
   After payment, the module automatically records the transaction into your database under the `clover_payment_transactions` table.

---

## 🪪 License  
This package is open-sourced software licensed under the **MIT License**.
