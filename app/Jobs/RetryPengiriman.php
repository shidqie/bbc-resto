<?php
namespace App\Jobs;

use App\Models\Pengiriman;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class RetryPengiriman implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $pengirimanId;
    protected $attempts = 0;

    /**
     * Create a new job instance.
     *
     * @param int $pengirimanId
     * @return void
     */
    public function __construct(int $pengirimanId)
    {
        $this->pengirimanId = $pengirimanId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $pengiriman = Pengiriman::find($this->pengirimanId);
        if (!$pengiriman) {
            Log::warning("RetryPengiriman: Pengiriman not found ID {$this->pengirimanId}");
            return;
        }

        try {
            // Reset status to "Siap" (status 2) so it can be retried.
            $pengiriman->update(['status_pengiriman_id' => 2]);
            Log::info("RetryPengiriman: Reset status to 2 for ID {$this->pengirimanId}");
        } catch (Exception $e) {
            Log::error("RetryPengiriman failed for ID {$this->pengirimanId}: " . $e->getMessage());
            // Re‑dispatch after a delay, up to 3 attempts
            if ($this->attempts < 3) {
                $this->attempts++;
                self::dispatch($this->pengirimanId)->delay(now()->addMinutes(5));
            }
        }
    }
}
?>
