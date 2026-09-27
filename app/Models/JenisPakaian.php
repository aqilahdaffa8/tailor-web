<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisPakaian extends Model
{
    use HasFactory;

    protected $table = 'jenis_pakaian';

    protected $fillable = [
        'kategori_pola_id',
        'nama_jenis',
        'deskripsi',
    ];

    /**
     * @return BelongsTo<KategoriPola, $this>
     */
    public function kategoriPola(): BelongsTo
    {
        return $this->belongsTo(KategoriPola::class, 'kategori_pola_id');
    }

    /**
     * @return HasMany<PolaDetail, $this>
     */
    public function polaDetail(): HasMany
    {
        return $this->hasMany(PolaDetail::class, 'jenis_pakaian_id');
    }
}
