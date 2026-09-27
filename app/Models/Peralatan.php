<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Peralatan extends Model
{
    use HasFactory;

    protected $table = 'peralatan';

    protected $fillable = [
        'nama_alat',
        'jumlah',
        'kondisi',
        'tanggal_beli',
        'catatan',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jumlah' => 'integer',
            'tanggal_beli' => 'date',
        ];
    }
}
