<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RiwayatStok extends Model
{
    use HasFactory;

    protected $table = 'riwayat_stok';

    protected $fillable = [
        'bahan_id',
        'jenis',
        'jumlah',
        'stok_sebelum',
        'stok_sesudah',
        'keterangan',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
            'stok_sebelum' => 'decimal:2',
            'stok_sesudah' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<Bahan, $this>
     */
    public function bahan(): BelongsTo
    {
        return $this->belongsTo(Bahan::class, 'bahan_id');
    }
}
