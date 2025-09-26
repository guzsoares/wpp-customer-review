<?php

namespace App\Console\Commands;

use App\Models\Appointment;
use App\Jobs\ScheduleReviewJob;
use Illuminate\Console\Command;

class SchedulePendingReviewsCommand extends Command
{

    protected $signature = 'reviews:schedule-pending 
    {--limit=50 : Limit the number of review requests to process} 
    {--dry-run : Show what would be done without making any changes}';

    protected $description = 'Schedule pending review requests';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $limit = (int) $this->option('limit');
        $dryRun = $this->option('dry-run');

        $this->info('Looking for completed appointments without review requests...');

        $appointments = Appointment::query()
            ->where('status', 'completed')
            ->whereDoesntHave('reviewRequest')
            ->where('appointment_datetime', '<=', now())
            ->limit($limit)
            ->get();

        if ($appointments->isEmpty()) {
            $this->info('No completed appointments found without review requests.');
            return 0;
        }

        $this->info('Found ' . $appointments->count() . ' appointments to process.');

        $this->table(
            ['ID', 'Customer Name', 'Phone', 'Service', 'Appointment DateTime'],
            $appointments->map(function ($appointment){
                return [
                    $appointment->id,
                    $appointment->customer_name,
                    $appointment->customer_phone,
                    $appointment->service_type,
                    $appointment->appointment_datetime->toDateTimeString(),
                ];
            })
        );

        if ($dryRun) {
            $this->info('Dry run mode - no changes will be made.');
            return 0;
        }

        $scheduled = 0;
        $failed = 0;

        foreach ($appointments as $appointment) {
            try {
                ScheduleReviewJob::dispatch($appointment);
                $this->info("Scheduled review request for Appointment ID {$appointment->id}");
                $scheduled++;
            } catch (\Exception $e) {
                $this->error("Failed to schedule review for Appointment ID {$appointment->id}: " . $e->getMessage());
                $failed++;
            }
        }

        $this->newLine();
        $this->info("Results:");
        $this->info(" Successfully scheduled: $scheduled");

        if ($failed > 0) {
            $this->info(" Failed to schedule: $failed");
        }

        $this->info("Review requests will be sent 1 hour after each appointment.");

        return 0;
    }
}
