<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HargaHarian extends Model
{
    protected $table = 'harga_harian';
    protected $guarded = ['id'];
    protected $casts = ['tanggal' => 'date', 'diverifikasi_pada' => 'datetime'];

    public const STATUS = ['draft', 'diajukan', 'disetujui', 'ditolak'];

    public function pasar()
    {
        return $this->belongsTo(Pasar::class);
    }

    public function komoditas()
    {
        return $this->belongsTo(Komoditas::class);
    }

    public function penginput()
    {
        return $this->belongsTo(User::class, 'diinput_oleh');
    }

    public function scopeDisetujui($q)
    {
        return $q->where('status', 'disetujui');
    }
}
