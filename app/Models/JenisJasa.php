<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JenisJasa extends Model
{
    use HasFactory;

    protected $table = 'jenis_jasa';

    protected $fillable = [
        'nama_jasa',
        'deskripsi',
        'harga_default',
        'is_active',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'harga_default' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    /**
     * @return HasMany<TemplateTahapan, $this>
     */
    public function templateTahapan(): HasMany
    {
        return $this->hasMany(TemplateTahapan::class, 'jenis_jasa_id')->orderBy('urutan');
    }

    /**
     * @return HasMany<ItemPesanan, $this>
     */
    public function itemPesanan(): HasMany
    {
        return $this->hasMany(ItemPesanan::class, 'jenis_jasa_id');
    }
}
