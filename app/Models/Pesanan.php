<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

class Pesanan extends Model
{
    use HasFactory;

    protected $table = 'pesanan';

    protected $fillable = [
        'nomor_pesanan',
        'pelanggan_id',
        'tanggal_pesan',
        'deadline',
        'catatan',
        'status',
    ];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'tanggal_pesan' => 'date',
            'deadline' => 'date',
        ];
    }

    /**
     * @return BelongsTo<Pelanggan, $this>
     */
    public function pelanggan(): BelongsTo
    {
        return $this->belongsTo(Pelanggan::class, 'pelanggan_id');
    }

    /**
     * @return HasMany<ItemPesanan, $this>
     */
    public function itemPesanan(): HasMany
    {
        return $this->hasMany(ItemPesanan::class, 'pesanan_id');
    }

    /**
     * @return HasMany<Pembayaran, $this>
     */
    public function pembayaran(): HasMany
    {
        return $this->hasMany(Pembayaran::class, 'pesanan_id');
    }

    /**
     * Hitung total tagihan dari seluruh item pesanan
     */
    public function getTotalHargaAttribute(): float
    {
        return (float) $this->itemPesanan()->sum('harga');
    }

    /**
     * Hitung total yang sudah dibayar
     */
    public function getTotalDibayarAttribute(): float
    {
        return (float) $this->pembayaran()->sum('jumlah');
    }

    /**
     * Hitung sisa pembayaran menggunakan Stored Function MySQL
     */
    public function getSisaPembayaranAttribute(): float
    {
        $result = DB::selectOne('SELECT fn_sisa_pembayaran(?) AS sisa', [$this->id]);

        return (float) ($result->sisa ?? 0);
    }

    /**
     * Ambil status pembayaran menggunakan Stored Function MySQL
     */
    public function getStatusPembayaranAttribute(): string
    {
        $result = DB::selectOne('SELECT fn_status_pembayaran(?) AS status', [$this->id]);

        return $result->status ?? 'Belum Bayar';
    }
}
