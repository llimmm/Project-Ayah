<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inflasi extends Model
{
    protected $table = 'inflasi';
    protected $guarded = ['id'];
    protected $casts = ['ihk' => 'float', 'mtm' => 'float', 'ytd' => 'float', 'yoy' => 'float'];

    public const WILAYAH = ['Provinsi NTB', 'Kota Mataram', 'Kota Bima', 'Kab. Sumbawa'];

    public function getLabelAttribute(): string
    {
        return bulan_singkat($this->bulan) . ' ' . $this->tahun;
    }
}
