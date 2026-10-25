<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

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
     * @var array<int, string>
     */
    protected $appends = ['foto_pola_url', 'file_pola_url'];

    /**
     * URL publik foto pola di disk public.
     */
    public function getFotoPolaUrlAttribute(): ?string
    {
        if (! $this->foto_pola) {
            return null;
        }

        return Storage::disk('public')->url($this->foto_pola);
    }

    /**
     * URL publik file pola di disk public.
     */
    public function getFilePolaUrlAttribute(): ?string
    {
        if (! $this->file_pola) {
            return null;
        }

        return Storage::disk('public')->url($this->file_pola);
    }

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
