<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class IphMingguan extends Model
{
    protected $table = 'iph_mingguan';
    protected $guarded = ['id'];
    protected $casts = ['perubahan_iph' => 'float'];

    public function getLabelAttribute(): string
    {
        return 'M' . $this->minggu_ke . ' ' . bulan_singkat($this->bulan) . ' ' . $this->tahun;
    }
}
