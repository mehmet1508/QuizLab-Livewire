<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    protected $fillable = ['ad'];

    public function quizzes()
    {
        return $this->hasMany(Quiz::class, 'kategori_id');
    }
}
