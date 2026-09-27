<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriBahan extends Model
{
    use HasFactory;

    protected $table = 'kategori_bahan';

    protected $fillable = [
        'nama_kategori',
    ];

    /**
     * @return HasMany<Bahan, $this>
     */
    public function bahan(): HasMany
    {
        return $this->hasMany(Bahan::class, 'kategori_bahan_id');
    }
}
