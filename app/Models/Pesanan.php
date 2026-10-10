<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
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
     * Pesanan yang masih dikerjakan.
     *
     * @param  Builder<Pesanan>  $query
     */
    public function scopeAktif(Builder $query): void
    {
        $query->where('status', 'aktif');
    }

    /**
     * Pesanan aktif yang deadline-nya sudah lewat.
     *
     * @param  Builder<Pesanan>  $query
     */
    public function scopeTerlambat(Builder $query): void
    {
        $query->where('status', 'aktif')
            ->whereDate('deadline', '<', now()->toDateString());
    }

    /**
     * Pesanan aktif dengan deadline dari hari ini sampai $hari hari ke depan.
     *
     * @param  Builder<Pesanan>  $query
     */
    public function scopeMendekatiDeadline(Builder $query, int $hari = 3): void
    {
        $query->where('status', 'aktif')
            ->whereBetween('deadline', [now()->toDateString(), now()->addDays($hari)->toDateString()]);
    }

    /**
     * Pesanan (selain yang dibatalkan) yang masih memiliki sisa tagihan.
     * Sisa dihitung oleh Stored Function fn_sisa_pembayaran.
     *
     * @param  Builder<Pesanan>  $query
     */
    public function scopeBelumLunas(Builder $query): void
    {
        $query->where('pesanan.status', '!=', 'dibatalkan')
            ->whereRaw('fn_sisa_pembayaran(pesanan.id) > 0');
    }

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
