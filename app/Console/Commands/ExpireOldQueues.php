<?php

namespace App\Console\Commands;
use App\Models\Queue;
use Illuminate\Console\Command;
use Carbon\Carbon;
class ExpireOldQueues extends Command
{
    protected $signature = 'queue:expire';
    protected $description = 'Tandai antrian lama yang belum selesai sebagai expired';
    public function handle()
    {
        $updated = Queue::where('status', 'waiting')
            ->whereDate('queue_date', '<', Carbon::today())
            ->update(['status' => 'skipped']);

        $this->info("Antrian expired: $updated");
    }

}
