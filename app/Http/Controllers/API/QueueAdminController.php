<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Queue;
use Carbon\Carbon;

class QueueAdminController extends Controller
{
    // Semua antrian hari ini
    public function today()
    {
        $queues = Queue::with('user')
            ->whereDate('queue_date', Carbon::today())
            ->orderBy('queue_number')
            ->get();

        return response()->json(['data' => $queues]);
    }

    // History semua antrian (filter optional)
    public function history(Request $request)
    {
        $query = Queue::with('user')->orderBy('queue_date', 'desc');

        if ($request->has('date')) {
            $query->whereDate('queue_date', $request->date);
        }

        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        return response()->json(['data' => $query->get()]);
    }

    // History antrian berdasarkan ID user
    public function userHistory($userId)
    {
        $queues = Queue::with('user')
            ->where('user_id', $userId)
            ->orderBy('queue_date', 'desc')
            ->get();

        return response()->json([
            'message' => $queues->isEmpty() ? 'Tidak ada riwayat antrian untuk user ini' : 'Riwayat antrian user ditemukan',
            'data' => $queues,
        ]);
    }


    // Statistik ringkas
    public function stats()
    {
        $today = Carbon::today();
        $yesterday = Carbon::yesterday();
        $weekStart = Carbon::now()->startOfWeek();

        return response()->json([
            'today' => Queue::whereDate('queue_date', $today)->count(),
            'yesterday' => Queue::whereDate('queue_date', $yesterday)->count(),
            'week' => Queue::whereBetween('queue_date', [$weekStart, $today])->count(),
            'all' => Queue::count(),
        ]);
    }

    // Antrian saat ini (yang paling awal statusnya waiting/processing)
    public function current()
    {
        $current = Queue::with('user')
            ->whereDate('queue_date', Carbon::today())
            ->whereIn('status', ['waiting', 'processing'])
            ->orderBy('queue_number', 'asc')
            ->first();

        return response()->json([
            'message' => $current ? 'Antrian saat ini' : 'Belum ada antrian berjalan',
            'data' => $current,
        ]);
    }

    // Ubah status jadi 'processing' dan pastikan hanya satu yg sedang diproses
    public function process($id)
    {
        // Tutup antrian lain yg sedang 'processing'
        Queue::whereDate('queue_date', Carbon::today())
            ->where('status', 'processing')
            ->update(['status' => 'skipped']); // atau 'done' sesuai kebutuhan

        $queue = Queue::findOrFail($id);
        $queue->update(['status' => 'processing']);

        return response()->json(['message' => 'Antrian sekarang diproses']);
    }

    public function finish($id)
    {
        $queue = Queue::findOrFail($id);
        $queue->update(['status' => 'done']);

        return response()->json(['message' => 'Antrian selesai']);
    }

    public function skip($id)
    {
        $queue = Queue::findOrFail($id);
        $queue->update(['status' => 'skipped']);

        return response()->json(['message' => 'Antrian dilewati']);
    }
}
