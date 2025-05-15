<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthCondition extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 
        'admin_id',
        'id_user_condition', // ✅ Tambahkan ini
        'tension', 
        'temperature', 
        'height', 
        'weight', 
        'spo2',
        'pulse', 
        'therapy', 
        'anamnesis',
        'status'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
