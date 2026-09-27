<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pelanggan extends Model
{
    use HasFactory;

    protected $table = 'pelanggan';

    protected $fillable = [
        'nama',
        'no_hp',
        'alamat',
        'catatan',
    ];

    /**
     * @return HasMany<Pesanan, $this>
     */
    public function pesanan(): HasMany
    {
        return $this->hasMany(Pesanan::class, 'pelanggan_id');
    }

    /**
     * @return HasMany<Pengukuran, $this>
     */
    public function pengukuran(): HasMany
    {
        return $this->hasMany(Pengukuran::class, 'pelanggan_id');
    }
}
