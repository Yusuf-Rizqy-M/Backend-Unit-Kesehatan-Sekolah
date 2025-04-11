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

        if ($user->role === 'admin') {
            $data = HealthCondition::with('user')->get();
        } else {
            $data = HealthCondition::where('user_id', $user->id)->get();
        }

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
        ]);

        $admin = Auth::user();
        if ($admin->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $data = HealthCondition::create([
            ...$request->only(['user_id', 'tension', 'temperature', 'height', 'weight', 'spo2']),
            'admin_id' => $admin->id,
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

        if (!$data) {
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
    

    public function update(Request $request, $id)
    {
        $request->validate([
            'tension' => 'required|integer',
            'temperature' => 'required|integer',
            'height' => 'required|numeric',
            'weight' => 'required|numeric',
            'spo2' => 'required|integer',
        ]);

        $admin = Auth::user();
        $data = HealthCondition::findOrFail($id);

        if ($admin->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $data->update($request->only(['tension', 'temperature', 'height', 'weight', 'spo2']));

        return response()->json([
            'status' => true,
            'data' => $data
        ]);
    }

    public function destroy($id)
    {
        $admin = Auth::user();
        $data = HealthCondition::findOrFail($id);

        if ($admin->role !== 'admin') {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ], 403);
        }

        $data->delete();
        return response()->json([
            'status' => true,
            'message' => 'Deleted'
        ]);
    }
}
