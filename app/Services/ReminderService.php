<?php

namespace App\Services;

use App\Models\Pesanan;
use App\Models\Reminder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Reminder otomatis. Kondisi dideteksi dari data (pesanan & stok), lalu disinkronkan ke tabel reminders:
 * - kondisi baru        -> reminder dibuat
 * - kondisi masih ada   -> reminder dipertahankan (status dibaca tidak berubah, teks diperbarui bila berbeda)
 * - kondisi sudah hilang -> reminder dihapus (mis. pesanan lunas/selesai, stok sudah diisi)
 * Reminder yang sudah di-dismiss tidak dibuat ulang selama kondisinya masih sama.
 */
class ReminderService
{
    public const TIPE = ['deadline', 'terlambat', 'pembayaran', 'siap_diambil', 'stok'];

    public function __construct(private readonly InventoryService $inventoryService) {}

    /**
     * Jalankan pengecekan dan sinkronkan tabel reminders.
     *
     * @return array{aktif: int, dibuat: int, diperbarui: int, dihapus: int}
     */
    public function sinkronkan(): array
    {
        $kondisi = $this->deteksiKondisi();

        $ada = Reminder::query()
            ->whereIn('tipe', self::TIPE)
            ->get()
            ->keyBy(fn (Reminder $reminder) => $this->kunci($reminder->tipe, $reminder->referensi_tipe, $reminder->referensi_id));

        $baru = $kondisi->diffKeys($ada);
        $kedaluwarsa = $ada->diffKeys($kondisi);
        $berubah = $kondisi->intersectByKeys($ada)->filter(
            fn (array $baris, string $kunci) => $ada[$kunci]->judul !== $baris['judul'] || $ada[$kunci]->pesan !== $baris['pesan']
        );

        DB::transaction(function () use ($baru, $kedaluwarsa, $berubah, $ada): void {
            $waktu = now();

            if ($baru->isNotEmpty()) {
                Reminder::insert(
                    $baru->map(fn (array $baris) => $baris + ['is_read' => false, 'created_at' => $waktu, 'updated_at' => $waktu])
                        ->values()
                        ->all()
                );
            }

            if ($kedaluwarsa->isNotEmpty()) {
                Reminder::whereIn('id', $kedaluwarsa->pluck('id'))->delete();
            }

            foreach ($berubah as $kunci => $baris) {
                $ada[$kunci]->update(['judul' => $baris['judul'], 'pesan' => $baris['pesan']]);
            }
        });

        return [
            'aktif' => $kondisi->count(),
            'dibuat' => $baru->count(),
            'diperbarui' => $berubah->count(),
            'dihapus' => $kedaluwarsa->count(),
        ];
    }

    /**
     * Reminder yang belum dibaca/di-dismiss, terbaru di atas.
     *
     * @return Collection<int, Reminder>
     */
    public function daftarAktif(?int $limit = null): Collection
    {
        return Reminder::query()
            ->belumDibaca()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->when($limit, fn ($query, int $limit) => $query->limit($limit))
            ->get();
    }

    public function jumlahBelumDibaca(): int
    {
        return Reminder::query()->belumDibaca()->count();
    }

    public function dismiss(Reminder $reminder): Reminder
    {
        $reminder->update(['is_read' => true]);

        return $reminder;
    }

    public function dismissSemua(): int
    {
        return Reminder::query()->belumDibaca()->update(['is_read' => true]);
    }

    /**
     * Semua kondisi yang saat ini harus memunculkan reminder, diindeks dengan kunci unik.
     *
     * @return Collection<string, array{tipe: string, referensi_tipe: string, referensi_id: int, judul: string, pesan: string}>
     */
    private function deteksiKondisi(): Collection
    {
        return collect([
            $this->deteksiDeadline(),
            $this->deteksiTerlambat(),
            $this->deteksiPembayaran(),
            $this->deteksiSiapDiambil(),
            $this->deteksiStok(),
        ])
            ->flatten(1)
            ->keyBy(fn (array $baris) => $this->kunci($baris['tipe'], $baris['referensi_tipe'], $baris['referensi_id']));
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function deteksiDeadline(): array
    {
        return Pesanan::mendekatiDeadline()->with('pelanggan')->get()
            ->map(fn (Pesanan $pesanan) => $this->baris(
                'deadline', 'pesanan', $pesanan->id,
                "Deadline mendekati: {$pesanan->nomor_pesanan}",
                "Pesanan {$pesanan->nomor_pesanan} ({$this->namaPelanggan($pesanan)}) jatuh tempo pada {$this->tanggal($pesanan)}.",
            ))->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function deteksiTerlambat(): array
    {
        return Pesanan::terlambat()->with('pelanggan')->get()
            ->map(fn (Pesanan $pesanan) => $this->baris(
                'terlambat', 'pesanan', $pesanan->id,
                "Pesanan terlambat: {$pesanan->nomor_pesanan}",
                "Pesanan {$pesanan->nomor_pesanan} ({$this->namaPelanggan($pesanan)}) melewati deadline {$this->tanggal($pesanan)} dan masih dikerjakan.",
            ))->all();
    }

    /**
     * Pesanan yang sudah selesai (atau sudah diambil) tetapi masih memiliki sisa tagihan.
     *
     * @return array<int, array<string, mixed>>
     */
    private function deteksiPembayaran(): array
    {
        return Pesanan::belumLunas()
            ->whereIn('pesanan.status', ['selesai', 'diambil'])
            ->select('pesanan.*')
            ->selectRaw('fn_sisa_pembayaran(pesanan.id) as sisa_tagihan')
            ->with('pelanggan')
            ->get()
            ->map(fn (Pesanan $pesanan) => $this->baris(
                'pembayaran', 'pesanan', $pesanan->id,
                "Pembayaran belum lunas: {$pesanan->nomor_pesanan}",
                "Pesanan {$pesanan->nomor_pesanan} ({$this->namaPelanggan($pesanan)}) sudah selesai tetapi masih ada sisa tagihan Rp "
                    .number_format((float) $pesanan->sisa_tagihan, 0, ',', '.').'.',
            ))->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function deteksiSiapDiambil(): array
    {
        return Pesanan::where('status', 'selesai')->with('pelanggan')->get()
            ->map(fn (Pesanan $pesanan) => $this->baris(
                'siap_diambil', 'pesanan', $pesanan->id,
                "Pesanan siap diambil: {$pesanan->nomor_pesanan}",
                "Pesanan {$pesanan->nomor_pesanan} ({$this->namaPelanggan($pesanan)}) sudah selesai dan menunggu diambil pelanggan.",
            ))->all();
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function deteksiStok(): array
    {
        return $this->inventoryService->getBahanMenipis()
            ->map(fn ($bahan) => $this->baris(
                'stok', 'bahan', $bahan->id,
                "Stok menipis: {$bahan->nama_bahan}",
                "Stok {$bahan->nama_bahan} tersisa {$this->angka($bahan->stok)} {$bahan->satuan} (minimum {$this->angka($bahan->stok_minimum)}).",
            ))->all();
    }

    /**
     * @return array{tipe: string, referensi_tipe: string, referensi_id: int, judul: string, pesan: string}
     */
    private function baris(string $tipe, string $referensiTipe, int $referensiId, string $judul, string $pesan): array
    {
        return [
            'tipe' => $tipe,
            'referensi_tipe' => $referensiTipe,
            'referensi_id' => $referensiId,
            'judul' => $judul,
            'pesan' => $pesan,
        ];
    }

    private function kunci(string $tipe, ?string $referensiTipe, ?int $referensiId): string
    {
        return $tipe.'|'.$referensiTipe.'|'.$referensiId;
    }

    private function namaPelanggan(Pesanan $pesanan): string
    {
        return $pesanan->pelanggan?->nama ?? 'tanpa nama';
    }

    private function tanggal(Pesanan $pesanan): string
    {
        return $pesanan->deadline->copy()->locale('id')->translatedFormat('j M Y');
    }

    private function angka(float|string $nilai): string
    {
        return rtrim(rtrim(number_format((float) $nilai, 2, ',', '.'), '0'), ',');
    }
}
