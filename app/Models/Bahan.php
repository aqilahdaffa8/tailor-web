<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bahan extends Model
{
    use HasFactory;

    protected $table = 'bahan';

    protected $fillable = [
        'kategori_bahan_id',
        'nama_bahan',
        'satuan',
        'stok',
        'stok_minimum',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'stok' => 'decimal:2',
            'stok_minimum' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<KategoriBahan, $this>
     */
    public function kategoriBahan(): BelongsTo
    {
        return $this->belongsTo(KategoriBahan::class, 'kategori_bahan_id');
    }

    /**
     * @return HasMany<PemakaianBahan, $this>
     */
    public function pemakaianBahan(): HasMany
    {
        return $this->hasMany(PemakaianBahan::class, 'bahan_id');
    }

    /**
     * @return HasMany<RiwayatStok, $this>
     */
    public function riwayatStok(): HasMany
    {
        return $this->hasMany(RiwayatStok::class, 'bahan_id')->latest();
    }

    /**
     * Cek apakah stok di bawah batas minimum
     */
    public function isMenipis(): bool
    {
        return $this->stok <= $this->stok_minimum;
    }
}
