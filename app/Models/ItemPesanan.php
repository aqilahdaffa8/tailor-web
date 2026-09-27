<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ItemPesanan extends Model
{
    use HasFactory;

    protected $table = 'item_pesanan';

    protected $fillable = [
        'pesanan_id',
        'jenis_jasa_id',
        'pola_detail_id',
        'pengukuran_id',
        'detail_pekerjaan',
        'harga',
        'deadline',
        'catatan',
        'status_item',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'harga' => 'decimal:2',
            'deadline' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Pesanan, $this>
     */
    public function pesanan(): BelongsTo
    {
        return $this->belongsTo(Pesanan::class, 'pesanan_id');
    }

    /**
     * @return BelongsTo<JenisJasa, $this>
     */
    public function jenisJasa(): BelongsTo
    {
        return $this->belongsTo(JenisJasa::class, 'jenis_jasa_id');
    }

    /**
     * @return BelongsTo<PolaDetail, $this>
     */
    public function polaDetail(): BelongsTo
    {
        return $this->belongsTo(PolaDetail::class, 'pola_detail_id');
    }

    /**
     * @return BelongsTo<Pengukuran, $this>
     */
    public function pengukuran(): BelongsTo
    {
        return $this->belongsTo(Pengukuran::class, 'pengukuran_id');
    }

    /**
     * @return HasMany<ProgressItem, $this>
     */
    public function progress(): HasMany
    {
        return $this->hasMany(ProgressItem::class, 'item_pesanan_id')->orderBy('urutan');
    }

    /**
     * @return HasMany<PemakaianBahan, $this>
     */
    public function pemakaianBahan(): HasMany
    {
        return $this->hasMany(PemakaianBahan::class, 'item_pesanan_id');
    }

    /**
     * @return HasMany<FotoReferensi, $this>
     */
    public function fotoReferensi(): HasMany
    {
        return $this->hasMany(FotoReferensi::class, 'item_pesanan_id');
    }
}
