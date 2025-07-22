<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Queue;
use Carbon\Carbon;

class ExpireOldQueues extends Command
{
    protected $signature = 'queue:expire';
    protected $description = 'Menandai antrian sebagai expired jika tanggal sudah lewat dan status masih waiting';

    public function handle()
    {
        $now = Carbon::now();

        $expiredQueues = Queue::where('status', 'waiting')
            ->where('queue_date', '<', $now)
            ->update(['status' => 'skipped']);

        $this->info("Antrian expired: {$expiredQueues}");
    }
}
