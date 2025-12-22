<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MidtransService;
use Illuminate\Support\Facades\Log;

class MidtransWebhookController extends Controller
{
    protected $midtrans;

    public function __construct(MidtransService $midtrans)
    {
        // No auth or CSRF here; route will be configured without CSRF middleware.
        $this->midtrans = $midtrans;
    }

    /**
     * Handle incoming Midtrans webhook notification.
     * Validates payload and delegates to MidtransService for processing.
     */
    public function handle(Request $request)
    {
        $raw = $request->getContent();
        $payload = json_decode($raw);

        if (!$payload) {
            Log::warning('Midtrans webhook received invalid JSON', ['body' => $raw]);
            return response('Bad Request', 400);
        }

        try {
            $processed = $this->midtrans->handleNotification($payload);
            if ($processed) {
                return response('OK', 200);
            }

            // If the service chose not to process (signature mismatch, unknown order), return 200
            // to avoid unnecessary retries from Midtrans.
            return response('Ignored', 200);
        } catch (\Exception $e) {
            Log::error('Midtrans webhook processing failed', ['error' => $e->getMessage(), 'payload' => $payload]);
            return response('Error', 500);
        }
    }
}
