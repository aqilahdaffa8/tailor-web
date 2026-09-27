<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pengukuran extends Model
{
    use HasFactory;

    protected $table = 'pengukuran';

    protected $fillable = [
        'pelanggan_id',
        'tanggal_ukur',
        'data_ukuran',
        'catatan',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_ukur' => 'date',
            'data_ukuran' => 'array',
        ];
    }

    /**
     * @return BelongsTo<Pelanggan, $this>
     */
    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    /**
     * @return HasMany<ItemPesanan, $this>
     */
    public function itemPesanan(): HasMany
    {
        return $this->hasMany(ItemPesanan::class, 'pengukuran_id');
    }
}
