<?php

namespace Supravatm\CloverPayment\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Supravatm\CloverPayment\Models\CloverPaymentTransaction;
use Supravatm\CloverPayment\Services\CloverClient;
use Illuminate\Support\Facades\File;

class CloverPaymentController extends Controller
{
    protected $clover;
    protected string $environment;
    protected string $logFile;

    public function __construct()
    {
        $this->clover = new CloverClient(config('clover'));
        $this->environment = $this->config['environment'] ?? 'sandbox';
        $this->logFile = $this->getLogFilePath();
    }
    /**
     * STEP 1: Tokenization handled via Clover API
     */
    public function createToken(Request $request)
    {
        $request->validate([
            'card.number' => 'required',
            'card.exp_month' => 'required',
            'card.exp_year' => 'required',
            'card.cvv' => 'required',
        ]);

        try {
            $response = $this->clover->createToken([
                'card' => [
                    'number' => $request->card['number'],
                    'exp_month' => $request->card['exp_month'],
                    'exp_year' => $request->card['exp_year'],
                    'cvv' => $request->card['cvv'],
                ]
            ]);

            return response()->json($response, 200);
        } catch (\Exception $e) {
            $this->log('error', 'Clover Tokenization Error', [
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Tokenization failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * STEP 2: Payment using Clover REST API
     */
    public function makePayment(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'amount' => 'required|numeric|min:0.01',
        ]);
        try {
            $amountInCents = intval($request->amount * 100);

            $randomNumber = mt_rand(1000, 9999);
            $prefixedNumber = "V" . $randomNumber;
            $orderId = $prefixedNumber;
            // Step 1: Create Order
            $transaction = CloverPaymentTransaction::create([
                'order_id' => $orderId,
                'amount' => $amountInCents,
                'status' => 'pending'
            ]);
            $order = $this->clover->createOrder($amountInCents, $orderId);

            if (empty($order['id'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to create order.',
                    'data' => $order,
                ], 400);
            }

            // Step 2: Make Payment linked to that order
            $payment = $this->clover->makePayment($order['id'], $request->token, $amountInCents);

            if (!empty($payment['id'])) {
                $transaction->update([
                    'transaction_id' => $payment['id'] ?? null,
                    'amount' => $amountInCents,
                    'status' => $payment['result'] ?? 'SUCCESS',
                    'response_payload' => json_encode($payment),
                ]);
                return response()->json([
                    'success' => true,
                    'message' => 'Payment successful',
                    'data' => $payment,
                    'order_id' => $order['id']
                ]);
            }

            return response()->json([
                'success' => false,
                'message' => $payment['message'] ?? 'Payment failed',
                'data' => $payment
            ], 400);
        } catch (\Exception $e) {
            $this->log('error', 'Clover Payment Error 1', [
                'message' => $e->getTraceAsString()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Server error: ' . $e->getMessage(),
            ], 500);
        }
    }
    /**
     * Custom environment-aware logging
     */
    protected function log(string $level, string $message, array $context = []): void
    {
        // Ensure logs directory exists
        if (!File::exists(storage_path('logs'))) {
            File::makeDirectory(storage_path('logs'), 0755, true);
        }

        $formatted = '[' . now() . "] {$message} " . json_encode($context, JSON_PRETTY_PRINT) . PHP_EOL;
        File::append($this->logFile, $formatted);

        // Also write to Laravel default log for visibility
        Log::{$level}($message, $context);
    }

    /**
     * Determine log file path based on environment
     */
    protected function getLogFilePath(): string
    {
        $filename = $this->environment === 'production'
            ? 'clover-production.log'
            : 'clover-sandbox.log';

        return storage_path("logs/{$filename}");
    }
}
