<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\HealthCondition;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class HealthConditionController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        $data = ($user->role === 'admin')
            ? HealthCondition::with('user')->get()
            : HealthCondition::where('user_id', $user->id)->get();

        return response()->json([
            'status' => true,
            'data' => $data,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
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

        $lastId = HealthCondition::where('user_id', $request->user_id)->max('id_user_condition') ?? 0;

        $data = HealthCondition::create([
            ...$request->only(['user_id', 'tension', 'temperature', 'height', 'weight', 'spo2', 'pulse', 'therapy', 'anamnesis']),
            'admin_id' => $admin->id,
            'id_user_condition' => $lastId + 1
        ]);

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function show()
    {
        $user = Auth::user();
        $data = HealthCondition::where('user_id', $user->id)->get();

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

    public function getByUserId($userId)
    {
        $authUser = Auth::user();

        if ($authUser->role !== 'admin' && $authUser->id != $userId) {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }

        $data = HealthCondition::where('user_id', $userId)->get();

        if ($data->isEmpty()) {
            return response()->json(['status' => false, 'message' => 'Data kondisi tidak ditemukan'], 404);
        }

        return response()->json(['status' => true, 'data' => $data]);
    }

    public function update(Request $request, $userId, $idUserCondition)
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

        $data = HealthCondition::where('user_id', $userId)
            ->where('id_user_condition', $idUserCondition)
            ->firstOrFail();

        $data->update($request->only([
            'tension', 'temperature', 'height', 'weight', 'spo2', 'pulse', 'therapy', 'anamnesis'
        ]));

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function destroy($userId, $idUserCondition)
    {
        $admin = Auth::user();
        if ($admin->role !== 'admin') {
            return response()->json(['status' => false, 'message' => 'Unauthorized'], 403);
        }
    
        $data = HealthCondition::where('user_id', $userId)
            ->where('id_user_condition', $idUserCondition)
            ->firstOrFail();
    
        // Update status dan id_user_condition
        $data->update([
            'status' => 'inactive',
            'id_user_condition' => null,
        ]);
    
        return response()->json([
            'status' => true,
            'message' => 'Condition marked as inactive and ID cleared'
        ]);
    }
    
}
