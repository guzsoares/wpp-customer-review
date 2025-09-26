<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    public function sendMessage(string $phone, string $message): bool
    {
        // For now, just log the message (we'll implement real WhatsApp later)
        Log::info("WhatsApp Message", [
            'to' => $phone,
            'message' => $message,
            'timestamp' => now()
        ]);

        // Simulate successful sending for testing
        return true;
    }
}