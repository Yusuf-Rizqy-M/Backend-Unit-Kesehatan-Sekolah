<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Department extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'desc', 'status'];

    public function grades()
    {
        return $this->hasMany(Grade::class);
    }
}
