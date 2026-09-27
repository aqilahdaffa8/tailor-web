<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ProgressItem extends Model
{
    use HasFactory;

    protected $table = 'progress_item';

    protected $fillable = [
        'item_pesanan_id',
        'nama_tahapan',
        'urutan',
        'is_completed',
        'completed_at',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'urutan' => 'integer',
            'is_completed' => 'boolean',
            'completed_at' => 'datetime',
        ];
    }

    /**
     * @return BelongsTo<ItemPesanan, $this>
     */
    public function itemPesanan(): BelongsTo
    {
        return $this->belongsTo(ItemPesanan::class, 'item_pesanan_id');
    }
}
