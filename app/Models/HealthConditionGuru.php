<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HealthConditionGuru extends Model
{
    use HasFactory;

    protected $table = 'health_condition_gurus';

    protected $fillable = [
        'guru_id',
        'admin_id',
        'id_guru_condition',
        'tension',
        'temperature',
        'height',
        'weight',
        'spo2',
        'pulse',
        'therapy',
        'anamnesis',
        'status',
    ];

    /**
     * Relasi ke tabel Guru
     */
    public function guru()
    {
        return $this->belongsTo(Guru::class, 'guru_id');
    }

    /**
     * Relasi ke tabel User (admin)
     */
    public function admin()
    {
        return $this->belongsTo(User::class, 'admin_id');
    }
}
