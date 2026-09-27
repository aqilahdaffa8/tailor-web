<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PolaDetail extends Model
{
    use HasFactory;

    protected $table = 'pola_detail';

    protected $fillable = [
        'jenis_pakaian_id',
        'size',
        'data_ukuran',
        'foto_pola',
        'file_pola',
        'catatan',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'data_ukuran' => 'array',
        ];
    }

    /**
     * @return BelongsTo<JenisPakaian, $this>
     */
    public function jenisPakaian(): BelongsTo
    {
        return $this->belongsTo(JenisPakaian::class, 'jenis_pakaian_id');
    }

    /**
     * @return HasMany<ItemPesanan, $this>
     */
    public function itemPesanan(): HasMany
    {
        return $this->hasMany(ItemPesanan::class, 'pola_detail_id');
    }
}
