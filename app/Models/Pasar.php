<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Pasar extends Model
{
    protected $table = 'pasar';
    protected $guarded = ['id'];
    protected $casts = ['aktif' => 'boolean'];

    public function scopeAktif($q)
    {
        return $q->where('aktif', true)->orderBy('nama');
    }
}
