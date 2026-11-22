<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Komoditas extends Model
{
    protected $table = 'komoditas';
    protected $guarded = ['id'];
    protected $casts = ['aktif' => 'boolean', 'masuk_iph' => 'boolean'];

    public function scopeAktif($q)
    {
        return $q->where('aktif', true)->orderBy('urutan')->orderBy('nama');
    }
}
