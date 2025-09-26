<?php

namespace App\Jobs;


use App\Models\Appointment;
use App\Models\ReviewRequest;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

class ScheduleReviewJob implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new job instance.
     */
    public function __construct(public Appointment $appointment)
    {
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (!$this->appointment->isCompleted()) {
            return;
        }

        if ($this->appointment->reviewRequest) {
            return;
        }

        $scheduledAt = $this->appointment->appointment_datetime->addHour();

        $this->appointment->reviewRequest()->create([
            'scheduled_at' => $scheduledAt,
            'status' => 'pending',
            'attempts' => 0
        ]);
    }
}
