<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\HealthConditionGuru;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HealthConditionGuruController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $data = ($user->role === 'admin')
            ? HealthConditionGuru::with('guru')->get()
            : HealthConditionGuru::where('guru_id', $user->id)->get();

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'guru_id' => 'required|exists:gurus,id',
            'tension' => 'required|integer',
            'temperature' => 'required|integer',
            'height' => 'required|numeric',
            'weight' => 'required|numeric',
            'spo2' => 'required|integer',
            'pulse' => 'required|integer',
            'therapy' => 'required|string',
            'anamnesis' => 'required|string',
        ]);

        $admin = Auth::user();
        if ($admin->role !== 'admin') {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $lastId = HealthConditionGuru::where('guru_id', $request->guru_id)->max('id_guru_condition') ?? 0;

        $data = HealthConditionGuru::create([
            ...$request->only(['guru_id', 'tension', 'temperature', 'height', 'weight', 'spo2', 'pulse', 'therapy', 'anamnesis']),
            'admin_id' => $admin->id,
            'id_guru_condition' => $lastId + 1
        ]);

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function show()
    {
        $user = Auth::user();
        $data = HealthConditionGuru::where('guru_id', $user->id)->get();

        if ($data->isEmpty()) {
            return response()->json([
                'status' => false,
                'message' => 'Data kondisi tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function getByGuruId($guruId)
    {
        $authUser = Auth::user();

        if ($authUser->role !== 'admin' && $authUser->id != $guruId) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $data = HealthConditionGuru::where('guru_id', $guruId)->get();

        if ($data->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'Data kondisi tidak ditemukan'], 404);
        }

        return response()->json(['status' => true, 'data' => $data]);
    }

    public function update(Request $request, $guruId, $idGuruCondition)
    {
        $request->validate([
            'tension' => 'required|integer',
            'temperature' => 'required|integer',
            'height' => 'required|numeric',
            'weight' => 'required|numeric',
            'spo2' => 'required|integer',
            'pulse' => 'required|integer',
            'therapy' => 'required|string',
            'anamnesis' => 'required|string',
        ]);

        $admin = Auth::user();
        if ($admin->role !== 'admin') {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $data = HealthConditionGuru::where('guru_id', $guruId)
            ->where('id_guru_condition', $idGuruCondition)
            ->firstOrFail();

        $data->update($request->only([
            'tension',
            'temperature',
            'height',
            'weight',
            'spo2',
            'pulse',
            'therapy',
            'anamnesis'
        ]));

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function destroy($guruId, $idGuruCondition)
    {
        $admin = Auth::user();
        if ($admin->role !== 'admin') {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $data = HealthConditionGuru::where('guru_id', $guruId)
            ->where('id_guru_condition', $idGuruCondition)
            ->firstOrFail();

        // Update status dan id_guru_condition
        $data->update([
            'status' => 'inactive',
            'id_guru_condition' => null,
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Condition marked as inactive and ID cleared'
        ]);
    }
       

}
