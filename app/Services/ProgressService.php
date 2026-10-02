<?php

namespace App\Services;

use App\Models\ItemPesanan;
use App\Models\Pesanan;
use App\Models\ProgressItem;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class ProgressService
{
    /**
     * Ambil tahapan yang sedang dikerjakan: tahapan pertama yang belum selesai,
     * atau tahapan terakhir jika seluruh tahapan sudah selesai.
     */
    public function tahapanSaatIni(ItemPesanan $item): ?ProgressItem
    {
        $progress = $item->progress()->get();

        return $progress->firstWhere('is_completed', false) ?? $progress->last();
    }

    /**
     * Ubah progress item sesuai aksi: "maju" atau "mundur".
     */
    public function ubahProgress(ItemPesanan $item, string $aksi): ItemPesanan
    {
        return $aksi === 'maju' ? $this->majukan($item) : $this->mundurkan($item);
    }

    /**
     * Majukan item ke tahapan berikutnya (tandai tahapan berjalan selesai).
     */
    public function majukan(ItemPesanan $item): ItemPesanan
    {
        $this->pastikanPesananAktif($item);

        DB::transaction(function () use ($item) {
            $berikutnya = $item->progress()
                ->where('is_completed', false)
                ->lockForUpdate()
                ->first();

            if (! $berikutnya) {
                throw ValidationException::withMessages([
                    'aksi' => 'Semua tahapan pada item ini sudah selesai.',
                ]);
            }

            $berikutnya->update([
                'is_completed' => true,
                'completed_at' => now(),
            ]);

            $this->sinkronkanStatusItem($item);
            $this->sinkronkanStatusPesanan($item->pesanan);
        });

        return $item->fresh(['progress']);
    }

    /**
     * Mundurkan item satu tahapan (batalkan tahapan selesai terakhir).
     */
    public function mundurkan(ItemPesanan $item): ItemPesanan
    {
        $this->pastikanPesananAktif($item);

        DB::transaction(function () use ($item) {
            $terakhir = $item->progress()
                ->where('is_completed', true)
                ->reorder('urutan', 'desc')
                ->lockForUpdate()
                ->first();

            if (! $terakhir) {
                throw ValidationException::withMessages([
                    'aksi' => 'Item ini masih berada di tahapan pertama, tidak bisa dimundurkan.',
                ]);
            }

            $terakhir->update([
                'is_completed' => false,
                'completed_at' => null,
            ]);

            $this->sinkronkanStatusItem($item);
            $this->sinkronkanStatusPesanan($item->pesanan);
        });

        return $item->fresh(['progress']);
    }

    /**
     * Perbarui kolom status_item: menunggu | diproses | selesai.
     */
    public function sinkronkanStatusItem(ItemPesanan $item): ItemPesanan
    {
        $status = $this->hitungStatusItem($item->progress()->get());

        if ($item->status_item !== $status) {
            $item->update(['status_item' => $status]);
        }

        return $item;
    }

    /**
     * Perbarui status pesanan dari kondisi progress seluruh itemnya:
     * aktif -> selesai (semua item mencapai tahap "Selesai") -> diambil (semua tahapan tuntas).
     * Pesanan yang dibatalkan tidak diubah.
     */
    public function sinkronkanStatusPesanan(Pesanan $pesanan): Pesanan
    {
        $pesanan->refresh();

        if ($pesanan->status === 'dibatalkan') {
            return $pesanan;
        }

        $items = $pesanan->itemPesanan()->with('progress')->get();

        $status = 'aktif';

        if ($items->isNotEmpty()) {
            $semuaTuntas = $items->every(
                fn (ItemPesanan $item) => $item->progress->isNotEmpty()
                    && $item->progress->every(fn (ProgressItem $tahap) => $tahap->is_completed)
            );

            $semuaSelesai = $items->every(
                fn (ItemPesanan $item) => $this->hitungStatusItem($item->progress) === 'selesai'
            );

            $status = $semuaTuntas ? 'diambil' : ($semuaSelesai ? 'selesai' : 'aktif');
        }

        if ($pesanan->status !== $status) {
            $pesanan->update(['status' => $status]);
        }

        return $pesanan;
    }

    /**
     * @param  Collection<int, ProgressItem>  $progress
     */
    public function hitungStatusItem(Collection $progress): string
    {
        $tuntas = $progress->where('is_completed', true);

        if ($progress->isEmpty() || $tuntas->isEmpty()) {
            return 'menunggu';
        }

        $sudahSelesai = $tuntas->count() === $progress->count()
            || $tuntas->contains(fn (ProgressItem $tahap) => strcasecmp($tahap->nama_tahapan, 'Selesai') === 0);

        return $sudahSelesai ? 'selesai' : 'diproses';
    }

    private function pastikanPesananAktif(ItemPesanan $item): void
    {
        if ($item->pesanan->status === 'dibatalkan') {
            throw ValidationException::withMessages([
                'aksi' => 'Progress tidak dapat diubah karena pesanan sudah dibatalkan.',
            ]);
        }
    }
}
