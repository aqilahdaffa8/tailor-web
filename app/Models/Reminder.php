<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reminder extends Model
{
    use HasFactory;

    protected $table = 'reminders';

    protected $fillable = [
        'judul',
        'pesan',
        'tipe',
        'referensi_id',
        'referensi_tipe',
        'is_read',
    ];

    /**
     * Reminder yang belum dibaca/di-dismiss.
     *
     * @param  Builder<Reminder>  $query
     */
    public function scopeBelumDibaca(Builder $query): void
    {
        $query->where('is_read', false);
    }

    /**
     * Tautan ke halaman terkait (pesanan atau inventory bahan); null bila tidak ada referensi.
     */
    public function getTautanAttribute(): ?string
    {
        return match ($this->referensi_tipe) {
            'pesanan' => $this->referensi_id ? route('pesanan.show', $this->referensi_id) : null,
            'bahan' => route('inventory.index', ['tab' => 'bahan']),
            default => null,
        };
    }

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_read' => 'boolean',
        ];
    }
}
