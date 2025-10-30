<?php

namespace Supravatm\CloverPayment\Http\Controllers;


use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Supravatm\CloverPayment\Services\CloverClient;
use Illuminate\Support\Facades\File;

class CloverOAuthController extends Controller
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

    public function handleCallback(Request $request)
    {
        $code = $request->query('code');
        if (!$code) {
            $this->log('ERROR', 'Authorization code missing');
            return response()->json([
                'success' => false,
                'message' => 'Authorization code missing',
            ], 400);
        }

        try {
            $response = $this->clover->handleCallback($code);

            // Save tokens securely (example: session)
            session([
                'clover_access_token' => $response['access_token']
            ]);

            return response()->json([
                'message' => 'OAuth flow completed successfully!',
                'response' => $response
            ], 200);
        } catch (\Exception $e) {
            $this->log('error', 'Failed to get access token from Clover', [
                'message' => $e->getMessage()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Failed to get access token from Clover',
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
