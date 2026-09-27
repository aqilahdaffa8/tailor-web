<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriPola extends Model
{
    use HasFactory;

    protected $table = 'kategori_pola';

    protected $fillable = [
        'nama_kategori',
        'deskripsi',
    ];

    /**
     * @return HasMany<JenisPakaian, $this>
     */
    public function jenisPakaian(): HasMany
    {
        return $this->hasMany(JenisPakaian::class, 'kategori_pola_id');
    }
}
