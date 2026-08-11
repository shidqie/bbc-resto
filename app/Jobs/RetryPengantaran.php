<?php
namespace App\Jobs;

use App\Models\Pengantaran;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RetryPengantaran implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $pengantaranId;
    protected $attempts = 0;

    /**
     * Create a new job instance.
     *
     * @param int $pengantaranId
     * @return void
     */
    public function __construct(int $pengantaranId)
    {
        $this->pengantaranId = $pengantaranId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $pengantaran = Pengantaran::find($this->pengantaranId);
        if (!$pengantaran) {
            Log::warning("RetryPengantaran: Pengantaran not found ID {$this->pengantaranId}");
            return;
        }

        try {
            // Reset status to "Siap" (status 2) so it can be retried.
            $pengantaran->update(['status_pengantaran_id' => 2]);
            Log::info("RetryPengantaran: Reset status to 2 for ID {$this->pengantaranId}");
        } catch (Exception $e) {
            Log::error("RetryPengantaran failed for ID {$this->pengantaranId}: " . $e->getMessage());
            // Re‑dispatch after a delay, up to 3 attempts
            if ($this->attempts < 3) {
                $this->attempts++;
                self::dispatch($this->pengantaranId)->delay(now()->addMinutes(5));
            }
        }
    }
}
?>
