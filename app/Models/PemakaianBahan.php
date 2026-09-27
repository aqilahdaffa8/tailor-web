<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PemakaianBahan extends Model
{
    use HasFactory;

    protected $table = 'pemakaian_bahan';

    protected $fillable = [
        'item_pesanan_id',
        'bahan_id',
        'jumlah',
        'catatan',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'jumlah' => 'decimal:2',
        ];
    }

    /**
     * @return BelongsTo<ItemPesanan, $this>
     */
    public function itemPesanan(): BelongsTo
    {
        return $this->belongsTo(ItemPesanan::class, 'item_pesanan_id');
    }

    /**
     * @return BelongsTo<Bahan, $this>
     */
    public function bahan(): BelongsTo
    {
        return $this->belongsTo(Bahan::class, 'bahan_id');
    }
}
