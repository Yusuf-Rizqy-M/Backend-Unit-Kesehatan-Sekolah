<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'image', 'description', 'status'];

    public function articles()
    {
        return $this->hasMany(Article::class);
    }
}