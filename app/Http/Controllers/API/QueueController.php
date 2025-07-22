<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use App\Models\Queue;
use Carbon\Carbon;

class QueueController extends Controller
{
    // 1. BUAT ANTRIAN
    public function store(Request $request)
    {
        $user = Auth::user();

        // Cek apakah user sudah punya antrian aktif (waiting/processing)
        $existingQueue = Queue::where('user_id', $user->id)
            ->whereIn('status', ['waiting', 'processing'])
            ->first();

        if ($existingQueue) {
            return response()->json([
                'message' => 'Kamu masih memiliki antrian yang belum selesai.',
            ], 409);
        }

        // Validasi reason
        $validated = Validator::make($request->all(), [
            'reason' => 'required|string|max:255',
        ]);

        if ($validated->fails()) {
            return response()->json([
                'message' => 'Validasi gagal',
                'errors' => $validated->errors()
            ], 422);
        }

        // Ambil antrian terakhir hari ini
        $lastQueue = Queue::whereDate('queue_date', Carbon::today())
            ->orderBy('queue_number', 'desc')
            ->first();

        $nextNumber = $lastQueue ? $lastQueue->queue_number + 1 : 1;

        $queue = Queue::create([
            'user_id' => $user->id,
            'queue_number' => $nextNumber,
            'reason' => $request->reason,
            'status' => 'waiting',
            'queue_date' => Carbon::today(),
        ]);

        return response()->json([
            'message' => 'Antrian berhasil dibuat',
            'data' => $queue
        ], 201);
    }

    // 2. MELIHAT ANTRIAN SAYA HARI INI
    public function myQueue()
    {
        $user = Auth::user();

        $todayQueues = Queue::where('user_id', $user->id)
            ->whereDate('queue_date', Carbon::today())
            ->orderBy('queue_number', 'asc')
            ->get();

        return response()->json($todayQueues);
    }

    // 3. MELIHAT ANTRIAN YANG MASIH BERLANGSUNG (WAITING / PROCESSING)
    public function currentQueue()
    {
        $user = Auth::user();

        $queue = Queue::where('user_id', $user->id)
            ->whereIn('status', ['waiting', 'processing'])
            ->whereDate('queue_date', Carbon::today())
            ->orderBy('queue_number', 'asc')
            ->first();

        return response()->json($queue);
    }

    // 4. MELIHAT HISTORY SAYA (SEMUA WAKTU)
    public function history()
    {
        $user = Auth::user();

        $history = Queue::where('user_id', $user->id)
            ->orderBy('queue_date', 'desc')
            ->get()
            ->map(function ($item) {
                $diffDays = Carbon::parse($item->queue_date)->diffInDays(Carbon::today());

                $item->day_label = match ($diffDays) {
                    0 => 'Hari ini',
                    1 => 'Kemarin',
                    default => "$diffDays hari yang lalu",
                };

                return $item;
            });

        return response()->json($history);
    }


    // 5. MELIHAT ANTRIAN YANG SEDANG DIPROSES (untuk tampilan user)
public function antrianSekarang()
{
    // Ambil antrian pertama yang status-nya masih waiting atau processing hari ini
    $currentQueue = Queue::whereDate('queue_date', Carbon::today())
        ->whereIn('status', ['waiting', 'processing'])
        ->orderBy('queue_number', 'asc')
        ->first();

    return response()->json([
        'message' => $currentQueue ? 'Antrian saat ini' : 'Belum ada antrian berjalan',
        'data' => [
            'queue_number' => $currentQueue->queue_number ?? 'N/A',
            'status' => $currentQueue->status ?? 'N/A',
            'reason' => $currentQueue->reason ?? 'N/A'
        ]
    ]);
}

    // 6. MEMBATALKAN ANTRIAN YANG MASIH AKTIF (waiting / processing)
    public function cancelQueue()
    {
        $user = Auth::user();

        $activeQueue = Queue::where('user_id', $user->id)
            ->whereIn('status', ['waiting', 'processing'])
            ->whereDate('queue_date', Carbon::today())
            ->first();

        if (!$activeQueue) {
            return response()->json([
                'message' => 'Tidak ada antrian aktif untuk dibatalkan.'
            ], 404);
        }

        $activeQueue->status = 'skipped';
        $activeQueue->save();

        return response()->json([
            'message' => 'Antrian berhasil dibatalkan.',
            'data' => $activeQueue
        ]);
    }
public function latestQueue()
{
    // Ambil antrian terakhir yang dibuat hari ini, tanpa mempedulikan status
    $latestQueue = Queue::whereDate('queue_date', Carbon::today())
        ->orderBy('queue_number', 'desc')
        ->first();

    return response()->json([
        'message' => $latestQueue ? 'Antrian terakhir hari ini' : 'Belum ada antrian hari ini',
        'data' => $latestQueue ? [
            'queue_number' => $latestQueue->queue_number,
            'status' => $latestQueue->status,
            'reason' => $latestQueue->reason
        ] : null
    ]);
}
// QueueController.php
public function checkQueueStatus()
{
    $user = Auth::user();

    $hasActiveQueue = Queue::where('user_id', $user->id)
        ->whereIn('status', ['waiting', 'processing'])
        ->whereDate('queue_date', Carbon::today())
        ->exists();

    return response()->json([
        'hasActiveQueue' => $hasActiveQueue
    ]);
}

// 1. Total antrian selesai hari ini
    public function totalCompletedToday()
    {
        $totalCompleted = Queue::whereDate('queue_date', Carbon::today())
            ->where('status', 'completed')
            ->count();

        return response()->json([
            'message' => 'Total antrian selesai hari ini',
            'data' => [
                'total_completed' => $totalCompleted
            ]
        ]);
    }

    // 2. Total antrian menunggu hari ini
    public function totalWaitingToday()
    {
        $totalWaiting = Queue::whereDate('queue_date', Carbon::today())
            ->where('status', 'waiting')
            ->count();

        return response()->json([
            'message' => 'Total antrian menunggu hari ini',
            'data' => [
                'total_waiting' => $totalWaiting
            ]
        ]);
    }

    // 3. Total antrian sedang diproses hari ini
    public function totalProcessingToday()
    {
        $totalProcessing = Queue::whereDate('queue_date', Carbon::today())
            ->where('status', 'processing')
            ->count();

        return response()->json([
            'message' => 'Total antrian sedang diproses hari ini',
            'data' => [
                'total_processing' => $totalProcessing
            ]
        ]);
    }

    // 4. Total antrian yang di-skip hari ini
    public function totalSkippedToday()
    {
        $totalSkipped = Queue::whereDate('queue_date', Carbon::today())
            ->where('status', 'skipped')
            ->count();

        return response()->json([
            'message' => 'Total antrian yang di-skip hari ini',
            'data' => [
                'total_skipped' => $totalSkipped
            ]
        ]);
    }
        public function totalUniqueQueueUsers()
    {
        $totalUniqueUsers = Queue::distinct('user_id')->count('user_id');

        return response()->json([
            'message' => 'Total user unik yang pernah antri',
            'data' => [
                'total_unique_users' => $totalUniqueUsers
            ]
        ]);
    }
}
