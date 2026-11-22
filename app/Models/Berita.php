<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Berita extends Model
{
    protected $table = 'berita';
    protected $guarded = ['id'];
    protected $casts = ['terbit_pada' => 'datetime'];

    public const KATEGORI = ['berita' => 'Berita', 'kegiatan' => 'Kegiatan TPID', 'dokumen' => 'Dokumen'];

    public function scopeTerbit($q)
    {
        return $q->whereNotNull('terbit_pada')->where('terbit_pada', '<=', now())->latest('terbit_pada');
    }

    public function penulis()
    {
        return $this->belongsTo(User::class, 'penulis_id');
    }
}
