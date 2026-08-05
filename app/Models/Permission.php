<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Permission extends Model
{
    protected $fillable = ['anahtar', 'etiket'];

    public function users()
    {
        return $this->belongsToMany(User::class, 'user_permission');
    }
}
