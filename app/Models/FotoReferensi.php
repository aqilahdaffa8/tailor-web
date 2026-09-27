<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FotoReferensi extends Model
{
    use HasFactory;

    protected $table = 'foto_referensi';

    protected $fillable = [
        'item_pesanan_id',
        'path_foto',
        'keterangan',
    ];

    /**
     * @return BelongsTo<ItemPesanan, $this>
     */
    public function itemPesanan(): BelongsTo
    {
        return $this->belongsTo(ItemPesanan::class, 'item_pesanan_id');
    }
}
