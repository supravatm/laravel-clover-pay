<?php

namespace Supravatm\CloverPayment\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\File;
use Exception;

class CloverClient
{
    protected Client $client;
    protected array $config;
    protected string $environment;
    protected string $logFile;

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->environment = $this->config['environment'] ?? 'sandbox';
        $this->logFile = $this->getLogFilePath();

        // Initialize Guzzle client
        $this->client = new Client([
            'base_uri' => rtrim($this->config['api_base'], '/') . '/',
            'timeout' => 30,
            'http_errors' => false,
        ]);
    }

    public function handleCallback($code)
    {
        try {
            $response = $this->client->post($this->config['oauth_url'], [
                'headers' => [
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'client_id' => $this->config['app_id'],
                    'client_secret' => $this->config['app_secret'],
                    'code' => $code,
                    'grant_type' => 'authorization_code',
                    'redirect_uri' => $this->config['redirect_url'],
                ],
            ]);

            $body = json_decode($response->getBody(), true);
            $status = $response->getStatusCode();

            if ($status >= 400) {
                $this->log('ERROR', 'Clover Oauth token failed', [
                    'status' => $status,
                    'body' => $body,
                ]);
                throw new Exception("Clover Oauth token failed: " . json_encode($body));
            }

            return $body;
        } catch (RequestException $e) {
            $this->log('ERROR', 'Failed to get oauth token from Clover', [
                'message' => $e->getMessage(),
                'response' => $e->getResponse()?->getBody()->getContents(),
            ]);
            throw new Exception('Failed to get access token from Clover: ' . $e->getMessage());
        }
    }
    /**
     * Create token from Clover API
     *
     * @param array $payload
     * @return void
     */
    public function createToken(array $payload)
    {
        try {
            $response = $this->client->post($this->config['token_url'], [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept'       => 'application/json',
                    'apikey'       => $this->config['public_key'],
                ],
                'json' => $payload,
            ]);

            $body = json_decode($response->getBody(), true);
            $status = $response->getStatusCode();

            $this->log('info', 'Clover create token response', [
                'status' => $status,
                'body' => $body,
            ]);

            if ($status >= 400) {
                throw new Exception("Clover tokenization failed: " . json_encode($body));
            }

            return $body;
        } catch (RequestException $e) {
            $this->log('error', 'Clover create token error', [
                'message' => $e->getMessage(),
                'response' => $e->getResponse()?->getBody()->getContents(),
            ]);
            throw new Exception('Clover token request failed: ' . $e->getMessage());
        }
    }

    /**
     * Create an order in Clover
     */
    public function createOrder(int $amountInCents, string $orderId = 'Order')
    {
        $this->log('info', 'Order request ', [
            "clover_item" => $cartItem = session('clover_item'),
        ]);

        // $endpoint = "{$this->config['merchant_id']}/orders";
        $endpoint = "{$this->config['merchant_id']}/atomic_order/orders";
        $cartItem = [
            "id" => "VV3MDJZSK275Y",
            "hidden" => false,
            "available" => true,
            "autoManage" => false,
            "name" => "Black Coffee (Each)",
            "price" => 50,
            "priceType" => "PER_UNIT",
            "defaultTaxRates" => true,
            "unitName" => "each",
            "isRevenue" => true,
            "modifiedTime" => 1761838101000,
            "deleted" => false,
        ];

        $lineItemId = $cartItem['id'];
        $note = "VITALOS";
        $currency = "usd";
        $lineItemPrice = $cartItem['price'];
        $lineItemQty = 1000;
        $this->log('info', 'clover_item', $cartItem);

        try {
            $response = $this->client->post($endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->config['access_token'],
                    'Content-Type'  => 'application/json',
                ],
                // 'json' => [
                //     'state' => 'open',
                //     'title' => $title,
                //     'total' => $amountInCents,
                // ],

                'json'  => [
                    "state" => "open",
                    "title" => $orderId,
                    "total" => $amountInCents,
                    "orderCart" => [
                        "lineItems" => [
                            [
                                "item" => ["id" => $lineItemId],
                                "price" => $lineItemPrice,
                                "unitQty" => $lineItemQty,
                            ],
                        ],
                        "merchant" => ["id" => $this->config['merchant_id']],
                        "id" => $orderId,
                        "currency" => $currency,
                        "title" => $orderId,
                        "note" => $note,
                    ],
                ]

            ]);

            $body = json_decode($response->getBody(), true);
            $status = $response->getStatusCode();

            $this->log('debug', 'Clover create order endpoint', [
                'url' => "{$this->config['api_base']}/{$endpoint}",
            ]);

            $this->log('info', 'Clover create order response', [
                'status' => $status,
                'body' => $body,
            ]);

            if ($status >= 400) {
                throw new Exception("Clover create order failed: " . json_encode($body));
            }

            return $body;
        } catch (RequestException $e) {
            $this->log('error', 'Clover create order exception', [
                'message' => $e->getMessage(),
                'response' => $e->getResponse()?->getBody()->getContents(),
            ]);
            throw new Exception('Clover create order request failed: ' . $e->getMessage());
        }
    }

    /**
     * Make payment for an order
     */
    public function makePayment(string $orderId, string $token, $amountInCents)
    {
        $endpoint = "{$this->config['merchant_id']}/payments";

        try {
            $response = $this->client->post($endpoint, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $this->config['access_token'],
                    'Content-Type'  => 'application/json',
                ],
                'json' => [
                    'order'  => ['id' => $orderId],
                    'amount' => $amountInCents,
                    'currency' => 'USD',
                    'source' => ['token' => $token],
                    'tender' => ['id' => $this->config['tender_id']],
                    'external_reference_id' => uniqid('order_'),
                ],
            ]);

            $body = json_decode($response->getBody(), true);
            $status = $response->getStatusCode();

            $this->log('info', 'Clover make payment response', [
                'status' => $status,
                'body' => $body,
            ]);

            if ($status >= 400) {
                throw new Exception("Clover payment failed: " . json_encode($body));
            }

            return $body;
        } catch (RequestException $e) {
            $this->log('error', 'Clover make payment exception', [
                'message' => $e->getMessage(),
                'response' => $e->getResponse()?->getBody()->getContents(),
            ]);
            throw new Exception('Clover payment request failed: ' . $e->getMessage());
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

        $formatted = '[' . now() . "] {$message} " . json_encode($context) . PHP_EOL;
        File::append($this->logFile, $formatted);

        // Also write to Laravel default log for visibility
        // Log::{$level}($message, $context);
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
