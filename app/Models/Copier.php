<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Copier extends Model
{
    use HasFactory;
    protected $fillable = [
        'name',
        'usage_date',
        'bw_counter',
        'color_counter',
        'total_counter',
        'limit',
        'bw_daily',
        'color_daily',
        'total_daily',
    ];
    protected $casts = [
        'usage_date' => 'date',
    ];

    public function latestReading()
    {
        // hasMany menghubungkan baris ini (hasil group by user_name)
        // dengan baris lain di tabel yang sama (CopyMachineReading)
        // melalui kolom user_name.
        return $this->hasMany(
            Copier::class,
            'name',
            'name'
        );
    }
}
