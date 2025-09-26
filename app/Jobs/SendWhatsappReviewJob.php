<?php

namespace App\Jobs;

use App\Models\ReviewRequest;
use App\Services\WhatsAppService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Log;

class SendWhatsappReviewJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public ReviewRequest $reviewRequest)
    {
        //
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if ($this->reviewRequest->status !== 'pending') {
            return;
        }

        if ($this->reviewRequest->attempts >= 3) {
            $this->reviewRequest->update(['status' => 'failed']);
            return;
        }

        try {
            $appointment = $this->reviewRequest->appointment;

            $message = "Olá {$appointment->customer_name}, esperamos que tenha sido bem atendido. Por favor, deixe sua avaliação (1-5)";

            $whatsAppService = new WhatsAppService();
            $sendingResult = $whatsAppService->sendMessage($appointment->customer_phone, $message);

            if ($sendingResult) {

                $this->reviewRequest->update([
                    'status' => 'sent',
                    'sent_at' => now(),
                    'attempts' => $this->reviewRequest->attempts + 1
                ]);
                
                    Log::info("WhatsApp review request sent", [
                        'appointment_id' => $appointment->id,
                        'customer_phone' => $appointment->customer_phone,
                        'timestamp' => now()
                    ]);
            } 
        } catch (\Exception $e) {
            $this->reviewRequest->increment('attempts');

            Log::error("Failed to send WhatsApp review request", [
                'error' => $e->getMessage(),
                'appointment_id' => $this->reviewRequest->appointment_id,
                'attempts' => $this->reviewRequest->attempts,
                'timestamp' => now()
            ]);

            if ($this->reviewRequest->attempts >= 3) {
                $this->reviewRequest->update(['status' => 'failed']);
            } else {
                self::dispatch($this->reviewRequest)->delay(now()->addMinutes(2));
            }
        }
    }
}
