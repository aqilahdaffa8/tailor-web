<?php

namespace App\Services;

use App\Models\Bahan;
use App\Models\FotoReferensi;
use App\Models\ItemPesanan;
use App\Models\JenisJasa;
use App\Models\Pelanggan;
use App\Models\PemakaianBahan;
use App\Models\Pembayaran;
use App\Models\Pengaturan;
use App\Models\Pengukuran;
use App\Models\Pesanan;
use App\Models\PolaDetail;
use App\Models\TemplateTahapan;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Arr;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use RuntimeException;
use Throwable;

class PesananService
{
    public function __construct(private readonly ProgressService $progressService) {}

    /**
     * Daftar pesanan dengan filter, pencarian, dan pagination.
     * Sisa tagihan & status bayar dihitung langsung oleh Stored Function MySQL.
     *
     * @param  array{search?: string, status?: string, pelanggan_id?: int|null, deadline?: string, pembayaran?: string}  $filters
     * @return LengthAwarePaginator<Pesanan>
     */
    public function getAll(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $hariIni = now()->toDateString();

        return Pesanan::query()
            ->select('pesanan.*')
            ->selectRaw('fn_sisa_pembayaran(pesanan.id) AS sisa_tagihan')
            ->selectRaw('fn_status_pembayaran(pesanan.id) AS status_bayar')
            ->with('pelanggan')
            ->withCount('itemPesanan')
            ->withSum('itemPesanan as total_tagihan', 'harga')
            ->when($filters['search'] ?? null, function ($query, string $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('nomor_pesanan', 'like', "%{$search}%")
                        ->orWhereHas('pelanggan', function ($p) use ($search) {
                            $p->where('nama', 'like', "%{$search}%")
                                ->orWhere('no_hp', 'like', "%{$search}%");
                        });
                });
            })
            ->when($filters['status'] ?? null, fn ($query, string $status) => $query->where('status', $status))
            ->when($filters['pelanggan_id'] ?? null, fn ($query, $id) => $query->where('pelanggan_id', $id))
            ->when($filters['deadline'] ?? null, function ($query, string $deadline) use ($hariIni) {
                match ($deadline) {
                    'terlambat' => $query->terlambat(),
                    'mendekati' => $query->mendekatiDeadline(),
                    'hari_ini' => $query->whereDate('deadline', $hariIni),
                    default => null,
                };
            })
            ->when(($filters['pembayaran'] ?? null) === 'belum_lunas', fn ($query) => $query->belumLunas())
            ->orderByRaw("FIELD(pesanan.status, 'aktif', 'selesai', 'diambil', 'dibatalkan')")
            ->orderBy('deadline')
            ->orderByDesc('pesanan.id')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Detail lengkap pesanan: header, item, progress, pemakaian bahan, foto, dan pembayaran.
     */
    public function find(int $id): Pesanan
    {
        $pesanan = Pesanan::with([
            'pelanggan',
            'itemPesanan' => fn ($query) => $query->orderBy('id'),
            'itemPesanan.jenisJasa',
            'itemPesanan.polaDetail.jenisPakaian',
            'itemPesanan.pengukuran',
            'itemPesanan.progress',
            'itemPesanan.pemakaianBahan.bahan',
            'itemPesanan.fotoReferensi',
            'pembayaran' => fn ($query) => $query->orderByDesc('tanggal_bayar')->orderByDesc('id'),
        ])->findOrFail($id);

        return $pesanan->append(['total_harga', 'total_dibayar', 'sisa_pembayaran', 'status_pembayaran']);
    }

    /**
     * Data pilihan untuk form pesanan (pelanggan, jenis jasa, pola, pengukuran).
     *
     * @return array<string, mixed>
     */
    public function getOpsiForm(): array
    {
        return [
            'daftarPelanggan' => Pelanggan::query()->orderBy('nama')->get(['id', 'nama', 'no_hp']),
            'daftarJasa' => JenisJasa::query()->where('is_active', true)->orderBy('nama_jasa')
                ->get(['id', 'nama_jasa', 'harga_default']),
            'daftarPola' => PolaDetail::query()->with('jenisPakaian:id,nama_jenis')
                ->orderBy('jenis_pakaian_id')->orderBy('size')->get(['id', 'jenis_pakaian_id', 'size']),
            'daftarPengukuran' => Pengukuran::query()->orderByDesc('tanggal_ukur')->orderByDesc('id')
                ->get(['id', 'pelanggan_id', 'tanggal_ukur']),
        ];
    }

    /**
     * Daftar bahan untuk form pemakaian bahan di detail pesanan.
     *
     * @return Collection<int, Bahan>
     */
    public function getDaftarBahan(): Collection
    {
        return Bahan::query()->orderBy('nama_bahan')->get(['id', 'nama_bahan', 'satuan', 'stok']);
    }

    /**
     * Buat pesanan baru: header + item pertama + salinan template tahapan dibuat atomik
     * oleh Stored Procedure sp_buat_pesanan (transaksi milik procedure itu sendiri).
     * Item tambahan dan DP dicatat setelahnya; bila gagal, pesanan dibersihkan kembali.
     *
     * Procedure tidak dibungkus DB::transaction karena START TRANSACTION di dalam
     * procedure akan meng-commit transaksi luar secara implisit.
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Pesanan
    {
        $items = array_values($data['items']);
        $total = collect($items)->sum(fn (array $item) => (float) $item['harga']);
        $dp = (float) ($data['dp'] ?? 0);

        if ($dp > $total) {
            throw ValidationException::withMessages([
                'dp' => 'Nominal DP tidak boleh melebihi total tagihan pesanan.',
            ]);
        }

        $pertama = $items[0];
        $hasil = null;

        for ($percobaan = 1; $percobaan <= 3; $percobaan++) {
            DB::statement(
                'CALL sp_buat_pesanan(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, @p_pesanan_id, @p_item_pesanan_id, @p_status_code, @p_status_message)',
                [
                    $this->buatNomorPesanan($data['tanggal_pesan']),
                    $data['pelanggan_id'],
                    $data['tanggal_pesan'],
                    $data['deadline'],
                    $data['catatan'] ?? null,
                    $pertama['jenis_jasa_id'],
                    $pertama['harga'],
                    $pertama['pola_detail_id'] ?? null,
                    $pertama['pengukuran_id'] ?? null,
                    $pertama['detail_pekerjaan'] ?? null,
                ]
            );

            $hasil = DB::selectOne(
                'SELECT @p_pesanan_id AS pesanan_id, @p_item_pesanan_id AS item_pesanan_id, @p_status_code AS status_code, @p_status_message AS status_message'
            );

            if ((int) $hasil->status_code === 200) {
                break;
            }
        }

        if ((int) $hasil->status_code !== 200) {
            throw new RuntimeException($hasil->status_message ?? 'Pesanan gagal dibuat.');
        }

        $pesanan = Pesanan::findOrFail($hasil->pesanan_id);

        try {
            $lengkapi = array_filter([
                'deadline' => $pertama['deadline'] ?? null,
                'catatan' => $pertama['catatan'] ?? null,
            ], fn ($nilai) => $nilai !== null && $nilai !== '');

            if ($lengkapi !== []) {
                ItemPesanan::whereKey($hasil->item_pesanan_id)->update($lengkapi);
            }

            DB::transaction(function () use ($pesanan, $items, $dp, $data) {
                foreach (array_slice($items, 1) as $item) {
                    $this->buatItem($pesanan, $item);
                }

                if ($dp > 0) {
                    Pembayaran::create([
                        'pesanan_id' => $pesanan->id,
                        'jumlah' => $dp,
                        'metode' => $data['metode_dp'] ?? 'tunai',
                        'tanggal_bayar' => now(),
                        'catatan' => 'DP awal pesanan',
                    ]);
                }
            });
        } catch (Throwable $e) {
            $pesanan->delete();

            throw $e;
        }

        return $this->find($pesanan->id);
    }

    /**
     * Update header pesanan (tanggal pesan, deadline, catatan).
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Pesanan $pesanan, array $data): Pesanan
    {
        $this->pastikanBisaDiubah($pesanan);

        $pesanan->update(Arr::only($data, ['tanggal_pesan', 'deadline', 'catatan']));

        return $this->find($pesanan->id);
    }

    /**
     * Batalkan pesanan. Riwayat pemakaian bahan tetap dipertahankan
     * karena bahan yang sudah dipotong tidak kembali ke stok.
     */
    public function batalkan(Pesanan $pesanan): Pesanan
    {
        if ($pesanan->status !== 'dibatalkan') {
            $pesanan->update(['status' => 'dibatalkan']);
        }

        return $pesanan->fresh();
    }

    /**
     * Tambah item ke pesanan yang sudah ada (template tahapan ikut disalin).
     *
     * @param  array<string, mixed>  $data
     */
    public function tambahItem(Pesanan $pesanan, array $data): ItemPesanan
    {
        $this->pastikanBisaDiubah($pesanan);

        $item = DB::transaction(fn () => $this->buatItem($pesanan, $data));

        $this->progressService->sinkronkanStatusPesanan($pesanan);

        return $item->load(['jenisJasa', 'progress']);
    }

    /**
     * Update data item (jenis jasa tidak dapat diganti karena tahapannya sudah tersalin).
     *
     * @param  array<string, mixed>  $data
     */
    public function updateItem(ItemPesanan $item, array $data): ItemPesanan
    {
        $this->pastikanBisaDiubah($item->pesanan);

        $item->update([
            'pola_detail_id' => $data['pola_detail_id'] ?? null,
            'pengukuran_id' => $data['pengukuran_id'] ?? null,
            'harga' => $data['harga'],
            'deadline' => $data['deadline'] ?? null,
            'detail_pekerjaan' => $data['detail_pekerjaan'] ?? null,
            'catatan' => $data['catatan'] ?? null,
        ]);

        return $item->fresh(['jenisJasa', 'progress']);
    }

    /**
     * Hapus item dari pesanan. Item terakhir dan item yang masih punya pemakaian bahan tidak boleh dihapus.
     */
    public function hapusItem(ItemPesanan $item): void
    {
        $pesanan = $item->pesanan;
        $this->pastikanBisaDiubah($pesanan);

        if ($pesanan->itemPesanan()->count() <= 1) {
            throw ValidationException::withMessages([
                'item' => 'Pesanan minimal harus memiliki satu item. Batalkan pesanan jika ingin membatalkan seluruhnya.',
            ]);
        }

        if ($item->pemakaianBahan()->exists()) {
            throw ValidationException::withMessages([
                'item' => 'Item ini sudah memiliki pemakaian bahan. Hapus pemakaian bahannya terlebih dahulu agar stok dikembalikan.',
            ]);
        }

        $pathFoto = $item->fotoReferensi()->pluck('path_foto')->all();

        $item->delete();

        Storage::disk('public')->delete($pathFoto);

        $this->progressService->sinkronkanStatusPesanan($pesanan);
    }

    /**
     * Catat pemakaian bahan lewat Stored Procedure sp_catat_pemakaian_bahan
     * (validasi stok + row lock + trigger pemotong stok).
     *
     * @param  array<string, mixed>  $data
     */
    public function catatPemakaianBahan(ItemPesanan $item, array $data): PemakaianBahan
    {
        $this->pastikanBisaDiubah($item->pesanan);

        DB::statement(
            'CALL sp_catat_pemakaian_bahan(?, ?, ?, ?, @p_status_code, @p_status_message)',
            [$item->id, $data['bahan_id'], $data['jumlah'], $data['catatan'] ?? null]
        );

        $hasil = DB::selectOne('SELECT @p_status_code AS status_code, @p_status_message AS status_message');
        $kode = (int) ($hasil->status_code ?? 500);

        if (in_array($kode, [400, 404], true)) {
            throw ValidationException::withMessages([
                $kode === 404 ? 'bahan_id' : 'jumlah' => $hasil->status_message,
            ]);
        }

        if ($kode !== 200) {
            throw new RuntimeException($hasil->status_message ?? 'Pemakaian bahan gagal dicatat.');
        }

        return PemakaianBahan::with('bahan')
            ->where('item_pesanan_id', $item->id)
            ->where('bahan_id', $data['bahan_id'])
            ->latest('id')
            ->firstOrFail();
    }

    /**
     * Hapus catatan pemakaian bahan. Trigger MySQL mengembalikan stok bahan otomatis.
     */
    public function hapusPemakaianBahan(PemakaianBahan $pemakaian): void
    {
        $this->pastikanBisaDiubah($pemakaian->itemPesanan->pesanan);

        $pemakaian->delete();
    }

    /**
     * Unggah foto referensi untuk sebuah item pesanan.
     */
    public function uploadFoto(ItemPesanan $item, UploadedFile $foto, ?string $keterangan = null): FotoReferensi
    {
        $this->pastikanBisaDiubah($item->pesanan);

        return $item->fotoReferensi()->create([
            'path_foto' => $foto->store('foto-referensi', 'public'),
            'keterangan' => $keterangan,
        ]);
    }

    /**
     * Hapus foto referensi beserta file-nya.
     */
    public function hapusFoto(FotoReferensi $foto): void
    {
        $path = $foto->path_foto;

        $foto->delete();

        Storage::disk('public')->delete($path);
    }

    /**
     * Buat item pesanan dan salin template tahapan jenis jasa menjadi progress_item.
     *
     * @param  array<string, mixed>  $data
     */
    private function buatItem(Pesanan $pesanan, array $data): ItemPesanan
    {
        $item = $pesanan->itemPesanan()->create([
            'jenis_jasa_id' => $data['jenis_jasa_id'],
            'pola_detail_id' => $data['pola_detail_id'] ?? null,
            'pengukuran_id' => $data['pengukuran_id'] ?? null,
            'detail_pekerjaan' => $data['detail_pekerjaan'] ?? null,
            'harga' => $data['harga'],
            'deadline' => ($data['deadline'] ?? null) ?: $pesanan->deadline->toDateString(),
            'catatan' => $data['catatan'] ?? null,
            'status_item' => 'menunggu',
        ]);

        TemplateTahapan::query()
            ->where('jenis_jasa_id', $item->jenis_jasa_id)
            ->orderBy('urutan')
            ->get()
            ->each(fn (TemplateTahapan $tahap) => $item->progress()->create([
                'nama_tahapan' => $tahap->nama_tahapan,
                'urutan' => $tahap->urutan,
                'is_completed' => false,
            ]));

        return $item;
    }

    /**
     * Nomor pesanan: {PREFIX}-{YYYYMMDD}-{urut 4 digit}, prefix dari pengaturan invoice.
     */
    private function buatNomorPesanan(string $tanggal): string
    {
        $invoice = Pengaturan::get('pengaturan_invoice', []);
        $prefix = is_array($invoice) ? (string) ($invoice['prefix'] ?? '') : '';
        $prefix = strtoupper((string) preg_replace('/[^A-Za-z0-9]/', '', $prefix)) ?: 'ORD';

        $awalan = $prefix.'-'.Carbon::parse($tanggal)->format('Ymd').'-';

        $terakhir = Pesanan::query()
            ->where('nomor_pesanan', 'like', $awalan.'%')
            ->orderByDesc('nomor_pesanan')
            ->value('nomor_pesanan');

        $urut = $terakhir ? ((int) substr($terakhir, strlen($awalan))) + 1 : 1;

        return $awalan.str_pad((string) $urut, 4, '0', STR_PAD_LEFT);
    }

    private function pastikanBisaDiubah(Pesanan $pesanan): void
    {
        if ($pesanan->status === 'dibatalkan') {
            throw ValidationException::withMessages([
                'pesanan' => 'Pesanan yang sudah dibatalkan tidak dapat diubah.',
            ]);
        }
    }
}
