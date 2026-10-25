<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

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
     * @var array<int, string>
     */
    protected $appends = ['foto_url'];

    /**
     * URL publik foto referensi di disk public.
     */
    public function getFotoUrlAttribute(): ?string
    {
        if (! $this->path_foto) {
            return null;
        }

        return Storage::disk('public')->url($this->path_foto);
    }

    /**
     * @return BelongsTo<ItemPesanan, $this>
     */
    public function itemPesanan(): BelongsTo
    {
        return $this->belongsTo(ItemPesanan::class, 'item_pesanan_id');
    }
}
