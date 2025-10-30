<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>Clover Checkout | Payment</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 40px auto;
            max-width: 400px;
            background: #f9f9f9;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        h2 {
            text-align: center;
        }

        input,
        button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
        }

        button {
            background: #006400;
            color: #fff;
            border: none;
            cursor: pointer;
            font-weight: bold;
        }

        button:hover {
            background: #228B22;
        }

        .result {
            margin-top: 20px;
            padding: 15px;
            border-radius: 8px;
            background: #e8f5e9;
            display: none;
        }
    </style>
</head>

<body>
    <h2>Checkout</h2>

    <h3>{{ $cart['product_name'] }}</h3>
    <p>Quantity: {{ $cart['quantity'] }}</p>
    <p>Price: {{ $cart['currency'] }} {{ number_format($cart['price'], 2) }}</p>

    <hr>
    <h4>Enter Card Details</h4>
    <form id="checkout-form">
        <input type="text" id="card-number" placeholder="Card Number" value="4242424242424242" required>
        <input type="text" id="exp-month" placeholder="MM" value="12" required>
        <input type="text" id="exp-year" placeholder="YY" value="29" required>
        <input type="text" id="cvv" placeholder="CVV" value="123" required>
        <input type="hidden" id="amount" value="{{ $cart['price'] }}">
        <button type="submit">Make Payment</button>
    </form>

    <div class="result" id="result-box"></div>

    <script>
        document.getElementById('checkout-form').addEventListener('submit', async (e) => {
            e.preventDefault();
            const resultBox = document.getElementById('result-box');
            resultBox.style.display = 'block';
            resultBox.innerHTML = '⏳ Processing payment...';

            const cardData = {
                card: {
                    number: document.getElementById('card-number').value,
                    exp_month: document.getElementById('exp-month').value,
                    exp_year: document.getElementById('exp-year').value,
                    cvv: document.getElementById('cvv').value
                }
            };

            try {
                // STEP 1: Get token from Laravel backend (no CORS)
                const tokenResponse = await fetch("/api/create-token", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json"
                    },
                    body: JSON.stringify(cardData)
                });
                const tokenData = await tokenResponse.json();

                if (!tokenData.id) {
                    resultBox.style.background = '#ffebee';
                    resultBox.innerHTML = '❌ Tokenization failed: ' + (tokenData.message || 'Unknown error');
                    return;
                }

                // STEP 2: Send token to Laravel to make payment
                const paymentResponse = await fetch("/api/make-payment", {
                    method: "POST",
                    headers: {
                        "Content-Type": "application/json",
                        "Accept": "application/json",
                    },
                    body: JSON.stringify({
                        token: tokenData.id,
                        amount: document.getElementById('amount').value
                    }),
                });

                const result = await paymentResponse.json();

                if (result.success) {
                    resultBox.style.background = '#e8f5e9';
                    resultBox.innerHTML =
                        `<strong>Payment Successful!</strong><br>
                        Transaction ID: ${result.data.id}<br>
                        Order ID: ${result.order_id}<br>
                        Status: ${result.data.result}`;
                } else {
                    resultBox.style.background = '#ffebee';
                    resultBox.innerHTML = `❌ Payment Failed: ${result.message}`;
                }

            } catch (err) {
                resultBox.style.background = '#ffebee';
                resultBox.innerHTML = `⚠️ Error: ${err.message}`;
            }
        });
    </script>
</body>

</html>