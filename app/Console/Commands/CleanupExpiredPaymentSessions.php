<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\PaymentSession;

class CleanupExpiredPaymentSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'payment-sessions:cleanup
                          {--dry-run : Show what would be cleaned up without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up expired payment sessions';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');
        
        $expiredCount = PaymentSession::where('expires_at', '<=', now())
                                     ->where('status', PaymentSession::STATUS_ACTIVE)
                                     ->count();

        if ($expiredCount === 0) {
            $this->info('No expired payment sessions found.');
            return Command::SUCCESS;
        }

        if ($dryRun) {
            $this->info("Found {$expiredCount} expired payment sessions that would be cleaned up.");
            return Command::SUCCESS;
        }

        $cleanedUp = PaymentSession::cleanupExpiredSessions();
        
        $this->info("Successfully cleaned up {$cleanedUp} expired payment sessions.");
        
        return Command::SUCCESS;
    }
}
