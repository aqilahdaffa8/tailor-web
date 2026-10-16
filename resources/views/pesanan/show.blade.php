<x-app-layout title="Detail Pesanan">
    <x-slot name="header">
        Detail Pesanan
    </x-slot>

    @php
        $dibatalkan = $pesanan->status === 'dibatalkan';
        $sisaHari = (int) today()->diffInDays($pesanan->deadline, false);
        $input = 'w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 placeholder:text-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 transition-colors';
        $metode = ['tunai' => 'Tunai', 'transfer' => 'Transfer', 'qris' => 'QRIS'];
    @endphp

    <div class="mb-5 flex items-center justify-between gap-3">
        <a href="{{ route('pesanan.index') }}" class="inline-flex items-center gap-2 text-sm text-zinc-500 hover:text-zinc-900 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar Pesanan
        </a>
    </div>

    {{-- Header --}}
    <div class="bg-white rounded-2xl border border-zinc-200 p-6 sm:p-8 mb-6 shadow-xs">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5">
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h2 class="text-xl font-bold text-zinc-900">{{ $pesanan->nomor_pesanan }}</h2>
                    @include('pesanan._badge', ['status' => $pesanan->status])
                    @include('pesanan._badge', ['status' => $pesanan->status_pembayaran])
                </div>
                <dl class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-x-10 gap-y-5">
                    <div>
                        <dt class="text-xs font-medium text-zinc-500 mb-1">Pelanggan</dt>
                        <dd class="text-sm">
                            <a href="{{ route('pelanggan.show', $pesanan->pelanggan) }}" class="font-semibold text-zinc-900 hover:underline">{{ $pesanan->pelanggan->nama }}</a>
                            @if($pesanan->pelanggan->no_hp)
                                <span class="block text-xs text-zinc-400 mt-1 tabular-nums">{{ $pesanan->pelanggan->no_hp }}</span>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-zinc-500 mb-1">Tanggal Pesan</dt>
                        <dd class="text-sm font-semibold text-zinc-900 tabular-nums">{{ $pesanan->tanggal_pesan->format('d M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-zinc-500 mb-1">Deadline</dt>
                        <dd class="text-sm font-semibold text-zinc-900 tabular-nums">
                            {{ $pesanan->deadline->format('d M Y') }}
                            @if($pesanan->status === 'aktif')
                                @if($sisaHari < 0)
                                    <span class="block text-xs font-semibold text-rose-600 mt-1">Terlambat {{ abs($sisaHari) }} hari</span>
                                @elseif($sisaHari <= 3)
                                    <span class="block text-xs font-semibold text-amber-700 mt-1">{{ $sisaHari === 0 ? 'Jatuh tempo hari ini' : 'H-'.$sisaHari }}</span>
                                @endif
                            @endif
                        </dd>
                    </div>
                </dl>
                @if($pesanan->catatan)
                    <p class="text-xs text-zinc-600 mt-6 bg-zinc-50 rounded-lg px-4 py-3 border border-zinc-200">{{ $pesanan->catatan }}</p>
                @endif
            </div>

            @unless($dibatalkan)
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('pesanan.edit', $pesanan) }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-zinc-200 bg-white text-xs font-medium text-zinc-700 hover:bg-zinc-50 transition-colors shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Edit
                    </a>
                    <form action="{{ route('pesanan.destroy', $pesanan) }}" method="POST"
                          onsubmit="return confirm('Batalkan pesanan {{ $pesanan->nomor_pesanan }}? Pesanan yang dibatalkan tidak dapat diubah lagi.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-rose-200 bg-white text-xs font-medium text-rose-700 hover:bg-rose-50 transition-colors shadow-xs cursor-pointer">
                            Batalkan Pesanan
                        </button>
                    </form>
                </div>
            @endunless
        </div>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">
        {{-- Kolom item --}}
        <div class="xl:col-span-2 space-y-6">
            @foreach($pesanan->itemPesanan as $item)
                @php
                    $tahapan = $item->progress->sortBy('urutan')->values();
                    $tahapanAktif = $tahapan->firstWhere('is_completed', false);
                    $tuntas = $tahapan->where('is_completed', true)->count();
                @endphp

                <div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden shadow-xs">
                    <div class="px-6 py-5 border-b border-zinc-200 bg-zinc-50/60 flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2.5">
                                <h3 class="text-sm font-semibold text-zinc-900">{{ $item->jenisJasa->nama_jasa ?? 'Item' }}</h3>
                                @include('pesanan._badge', ['status' => $item->status_item])
                            </div>
                            <p class="text-xs text-zinc-500 mt-1">
                                {{ $item->detail_pekerjaan ?: 'Tanpa detail pekerjaan' }}
                                @if($item->polaDetail)
                                    &middot; Pola {{ $item->polaDetail->jenisPakaian->nama_jenis ?? '' }} {{ $item->polaDetail->size }}
                                @endif
                                @if($item->pengukuran)
                                    &middot; Ukuran {{ $item->pengukuran->tanggal_ukur->format('d M Y') }}
                                @endif
                            </p>
                            @if($item->catatan)
                                <p class="text-xs text-zinc-600 mt-1">Catatan: {{ $item->catatan }}</p>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="font-bold text-zinc-900 tabular-nums">Rp {{ number_format((float) $item->harga, 0, ',', '.') }}</p>
                            @if($item->deadline)
                                <p class="text-xs text-zinc-500 tabular-nums">Deadline {{ $item->deadline->format('d M Y') }}</p>
                            @endif
                        </div>
                    </div>

                    <div>
                        {{-- Progress jahitan --}}
                        <div class="px-6 py-6">
                            <div class="flex flex-wrap items-center justify-between gap-3 mb-5">
                                <h4 class="text-xs font-semibold text-zinc-900 uppercase tracking-wider">Progress Pengerjaan ({{ $tuntas }}/{{ $tahapan->count() }})</h4>
                                @if(! $dibatalkan && $tahapan->isNotEmpty())
                                    <div class="flex items-center gap-2">
                                        <form action="{{ route('pesanan.item.progress', $item) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="aksi" value="mundur">
                                            <button type="submit" @disabled($tuntas === 0)
                                                    class="px-3 py-1.5 rounded-lg border border-zinc-200 bg-white text-xs font-medium text-zinc-700 hover:bg-zinc-50 transition-colors shadow-xs cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                                                Mundurkan
                                            </button>
                                        </form>
                                        <form action="{{ route('pesanan.item.progress', $item) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="aksi" value="maju">
                                            <button type="submit" @disabled(! $tahapanAktif)
                                                    class="px-3 py-1.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium transition-colors shadow-xs cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                                                Selesaikan: {{ $tahapanAktif?->nama_tahapan ?? 'Semua tahap tuntas' }}
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </div>

                            @if($tahapan->isEmpty())
                                <p class="text-xs text-zinc-500 bg-zinc-50 border border-zinc-200 rounded-lg px-4 py-3">
                                    Item ini belum memiliki tahapan pengerjaan karena jenis jasanya belum punya template tahapan.
                                    Atur template tahapan di menu Pengaturan, lalu tambahkan ulang item ini.
                                </p>
                            @endif

                            <ol class="space-y-0">
                                @foreach($tahapan as $tahap)
                                    @php $berjalan = $tahapanAktif && $tahapanAktif->id === $tahap->id; @endphp
                                    <li class="flex gap-3">
                                        <div class="flex flex-col items-center">
                                            <span class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 border-2
                                                {{ $tahap->is_completed ? 'bg-emerald-600 border-emerald-600 text-white' : ($berjalan ? 'border-zinc-900 bg-zinc-100 text-zinc-900 ring-2 ring-zinc-200' : 'border-zinc-200 bg-white') }}">
                                                @if($tahap->is_completed)
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                @elseif($berjalan)
                                                    <span class="w-2 h-2 rounded-full bg-zinc-900"></span>
                                                @endif
                                            </span>
                                            @unless($loop->last)
                                                <span class="w-0 flex-1 min-h-5 border-l-2 {{ $tahap->is_completed ? 'border-emerald-600' : 'border-zinc-200' }}"></span>
                                            @endunless
                                        </div>
                                        <div class="pb-4 -mt-0.5">
                                            <p class="text-sm {{ $tahap->is_completed ? 'text-zinc-900 font-medium' : ($berjalan ? 'text-zinc-900 font-semibold' : 'text-zinc-400') }}">
                                                {{ $tahap->nama_tahapan }}
                                            </p>
                                            @if($tahap->completed_at)
                                                <p class="text-xs text-zinc-400 tabular-nums">{{ $tahap->completed_at->format('d M Y, H:i') }}</p>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ol>
                        </div>

                        {{-- Pemakaian bahan --}}
                        <div class="px-6 py-6 border-t border-zinc-200">
                            <h4 class="text-xs font-semibold text-zinc-900 uppercase tracking-wider mb-4">Pemakaian Bahan</h4>

                            @forelse($item->pemakaianBahan as $pemakaian)
                                <div class="flex items-center justify-between gap-4 py-3 border-b border-zinc-100 last:border-0">
                                    <div>
                                        <p class="text-sm font-medium text-zinc-900">{{ $pemakaian->bahan->nama_bahan }}</p>
                                        <p class="text-xs text-zinc-500 tabular-nums">
                                            {{ rtrim(rtrim(number_format((float) $pemakaian->jumlah, 2, ',', '.'), '0'), ',') }} {{ $pemakaian->bahan->satuan }}
                                            @if($pemakaian->catatan) &middot; {{ $pemakaian->catatan }} @endif
                                        </p>
                                    </div>
                                    @unless($dibatalkan)
                                        <form action="{{ route('pesanan.pemakaian.destroy', $pemakaian) }}" method="POST"
                                              onsubmit="return confirm('Hapus pemakaian bahan ini? Stok akan dikembalikan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs font-medium text-rose-600 hover:text-rose-700 cursor-pointer">Hapus</button>
                                        </form>
                                    @endunless
                                </div>
                            @empty
                                <p class="text-xs text-zinc-500">Belum ada bahan yang dipakai untuk item ini.</p>
                            @endforelse

                            @unless($dibatalkan)
                                <form action="{{ route('pesanan.item.bahan.store', $item) }}" method="POST"
                                      class="mt-6 pt-6 border-t border-zinc-200 flex flex-col gap-4">
                                    @csrf
                                    <div>
                                        <label class="block text-xs font-medium text-zinc-700 mb-1">Bahan</label>
                                        <select name="bahan_id" required class="{{ $input }}">
                                            <option value="">-- Pilih Bahan --</option>
                                            @foreach($daftarBahan as $bahan)
                                                <option value="{{ $bahan->id }}">
                                                    {{ $bahan->nama_bahan }} (stok {{ rtrim(rtrim(number_format((float) $bahan->stok, 2, ',', '.'), '0'), ',') }} {{ $bahan->satuan }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-medium text-zinc-700 mb-1">Jumlah</label>
                                            <input type="number" name="jumlah" min="0.01" step="0.01" required placeholder="Contoh: 2,5" class="{{ $input }} tabular-nums">
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-zinc-700 mb-1">Catatan (opsional)</label>
                                            <input type="text" name="catatan" placeholder="Contoh: untuk badan" class="{{ $input }}">
                                        </div>
                                    </div>
                                    <div>
                                        <button type="submit"
                                                class="px-4 py-2 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium transition-colors shadow-xs cursor-pointer">
                                            Catat Pemakaian
                                        </button>
                                    </div>
                                </form>
                            @endunless
                        </div>

                        {{-- Foto referensi --}}
                        <div class="px-6 py-6 border-t border-zinc-200">
                            <h4 class="text-xs font-semibold text-zinc-900 uppercase tracking-wider mb-4">Foto Referensi</h4>

                            @if($item->fotoReferensi->isNotEmpty())
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                                    @foreach($item->fotoReferensi as $foto)
                                        <div class="group relative rounded-xl overflow-hidden border border-zinc-200 bg-zinc-50 shadow-xs">
                                            <a href="{{ asset('storage/'.$foto->path_foto) }}" target="_blank" rel="noopener">
                                                <img src="{{ asset('storage/'.$foto->path_foto) }}" alt="{{ $foto->keterangan ?: 'Foto referensi' }}"
                                                     class="w-full h-28 object-cover">
                                            </a>
                                            @if($foto->keterangan)
                                                <p class="px-2 py-1.5 text-xs text-zinc-700 truncate">{{ $foto->keterangan }}</p>
                                            @endif
                                            @unless($dibatalkan)
                                                <form action="{{ route('pesanan.foto.destroy', $foto) }}" method="POST"
                                                      class="absolute top-1.5 right-1.5 opacity-0 group-hover:opacity-100 transition-opacity"
                                                      onsubmit="return confirm('Hapus foto ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" aria-label="Hapus foto"
                                                            class="w-6 h-6 rounded-full bg-rose-600 text-white flex items-center justify-center cursor-pointer shadow-xs">
                                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                        </svg>
                                                    </button>
                                                </form>
                                            @endunless
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <p class="text-xs text-zinc-500">Belum ada foto referensi.</p>
                            @endif

                            @unless($dibatalkan)
                                <form action="{{ route('pesanan.item.foto.store', $item) }}" method="POST" enctype="multipart/form-data"
                                      class="mt-6 pt-6 border-t border-zinc-200 flex flex-col gap-4">
                                    @csrf
                                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-xs font-medium text-zinc-700 mb-1">Berkas Foto</label>
                                            <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" required
                                                   class="{{ $input }} text-xs file:mr-3 file:px-3 file:py-1 file:rounded-md file:border-0 file:bg-zinc-100 file:text-zinc-700 file:font-medium">
                                            <p class="text-xs text-zinc-400 mt-1.5">JPG, PNG, atau WebP. Maksimal 5 MB.</p>
                                        </div>
                                        <div>
                                            <label class="block text-xs font-medium text-zinc-700 mb-1">Keterangan (opsional)</label>
                                            <input type="text" name="keterangan" placeholder="Contoh: model kerah" class="{{ $input }}">
                                        </div>
                                    </div>
                                    <div>
                                        <button type="submit"
                                                class="px-4 py-2 rounded-lg border border-zinc-200 bg-white text-xs font-medium text-zinc-700 hover:bg-zinc-50 transition-colors shadow-xs cursor-pointer">
                                            Unggah Foto
                                        </button>
                                    </div>
                                </form>
                            @endunless
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Kolom pembayaran --}}
        <div class="space-y-6"
             x-data="{ buka: false, jumlah: '', sisa: {{ (int) round($pesanan->sisa_pembayaran) }},
                       lunasi() { this.jumlah = this.sisa; this.buka = true; } }"
             @keydown.escape.window="buka = false">

            {{-- Banner alur: Selesai -> Cek Sisa -> Bayar -> Diambil --}}
            @if($pesanan->status === 'selesai')
                @if($pesanan->sisa_pembayaran > 0)
                    <div class="rounded-2xl bg-amber-50 border border-amber-200 p-4 text-sm text-amber-900 shadow-xs">
                        <p class="font-semibold text-amber-900">Pesanan sudah selesai dijahit</p>
                        <p class="text-xs text-amber-800 mt-1">Sisa pembayaran <strong class="tabular-nums">Rp {{ number_format((float) $pesanan->sisa_pembayaran, 0, ',', '.') }}</strong> perlu dilunasi sebelum barang diambil pelanggan.</p>
                        <button type="button" @click="lunasi()"
                                class="mt-3 px-3 py-1.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium transition-colors shadow-xs cursor-pointer">
                            Lunasi Sisa Sekarang
                        </button>
                    </div>
                @else
                    <div class="rounded-2xl bg-emerald-50 border border-emerald-200 p-4 text-sm text-emerald-900 shadow-xs">
                        <p class="font-semibold text-emerald-900">Pesanan lunas dan siap diambil</p>
                        <p class="text-xs text-emerald-800 mt-1">Selesaikan tahap pengambilan pada item untuk menutup pesanan ini.</p>
                    </div>
                @endif
            @endif

            <div class="bg-white rounded-2xl border border-zinc-200 p-6 shadow-xs">
                <div class="flex items-center justify-between mb-4">
                    <h3 class="text-base font-semibold text-zinc-900">Ringkasan Pembayaran</h3>
                    @include('pesanan._badge', ['status' => $pesanan->status_pembayaran])
                </div>

                <div class="rounded-xl bg-zinc-50 border border-zinc-200 p-4 space-y-2.5">
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-600">Total tagihan</span>
                        <span class="font-semibold text-zinc-900 tabular-nums">Rp {{ number_format((float) $pesanan->total_harga, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-600">Sudah dibayar</span>
                        <span class="font-medium text-emerald-600 tabular-nums">Rp {{ number_format((float) $pesanan->total_dibayar, 0, ',', '.') }}</span>
                    </div>
                    <div class="border-t border-zinc-200 pt-2.5 flex justify-between text-sm">
                        <span class="text-zinc-600 font-medium">Sisa tagihan</span>
                        <span class="font-bold text-zinc-900 tabular-nums">Rp {{ number_format((float) $pesanan->sisa_pembayaran, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-2">
                    @if(! $dibatalkan && $pesanan->sisa_pembayaran > 0)
                        <button type="button" @click="jumlah = ''; buka = true"
                                class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium transition-colors shadow-xs cursor-pointer">
                            <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Input Pembayaran
                        </button>
                    @endif
                    <a href="{{ route('pesanan.invoice', $pesanan) }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-zinc-200 bg-white text-xs font-medium text-zinc-700 hover:bg-zinc-50 transition-colors shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                        </svg>
                        Cetak Invoice
                    </a>
                    <a href="{{ route('pesanan.invoice.pdf', $pesanan) }}"
                       class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-zinc-200 bg-white text-xs font-medium text-zinc-700 hover:bg-zinc-50 transition-colors shadow-xs">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Unduh PDF
                    </a>
                </div>

                <h4 class="text-xs font-semibold text-zinc-900 uppercase tracking-wider mt-6 mb-2">Riwayat Pembayaran</h4>
                @forelse($pesanan->pembayaran as $bayar)
                    <div class="flex items-center justify-between gap-3 py-2.5 border-b border-zinc-100 last:border-0">
                        <div>
                            <p class="text-sm font-medium text-zinc-900 tabular-nums">Rp {{ number_format((float) $bayar->jumlah, 0, ',', '.') }}</p>
                            <p class="text-xs text-zinc-500 tabular-nums">
                                {{ $metode[$bayar->metode] ?? ucfirst($bayar->metode) }} &middot; {{ $bayar->tanggal_bayar->format('d M Y') }}
                                @if($bayar->catatan) &middot; {{ $bayar->catatan }} @endif
                            </p>
                        </div>
                        <form action="{{ route('pembayaran.destroy', $bayar) }}" method="POST"
                              onsubmit="return confirm('Batalkan pembayaran Rp {{ number_format((float) $bayar->jumlah, 0, ',', '.') }}?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-xs font-medium text-rose-600 hover:text-rose-700 cursor-pointer">Batalkan</button>
                        </form>
                    </div>
                @empty
                    <p class="text-xs text-zinc-500">Belum ada pembayaran.</p>
                @endforelse
            </div>

            {{-- Modal input pembayaran --}}
            @if(! $dibatalkan && $pesanan->sisa_pembayaran > 0)
            <div x-show="buka" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4">
                <div class="absolute inset-0 bg-zinc-900/60" @click="buka = false"></div>
                <form action="{{ route('pesanan.pembayaran.store', $pesanan) }}" method="POST"
                      class="relative w-full max-w-md bg-white rounded-2xl border border-zinc-200 p-6 space-y-4 shadow-xl">
                    @csrf
                    <div class="flex items-center justify-between">
                        <h3 class="text-base font-semibold text-zinc-900">Input Pembayaran</h3>
                        <button type="button" @click="buka = false" aria-label="Tutup" class="text-zinc-400 hover:text-zinc-600 cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    <p class="text-xs text-zinc-500">Sisa tagihan: <strong class="text-zinc-900 tabular-nums">Rp {{ number_format((float) $pesanan->sisa_pembayaran, 0, ',', '.') }}</strong></p>

                    <div>
                        <label class="block text-xs font-medium text-zinc-700 mb-1">Jumlah (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" name="jumlah" min="1" step="1" required x-model="jumlah" class="{{ $input }} tabular-nums">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-zinc-700 mb-1">Metode <span class="text-rose-500">*</span></label>
                            <select name="metode" required class="{{ $input }}">
                                @foreach($metode as $nilai => $labelMetode)
                                    <option value="{{ $nilai }}">{{ $labelMetode }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-700 mb-1">Tanggal</label>
                            <input type="date" name="tanggal_bayar" max="{{ date('Y-m-d') }}" value="{{ date('Y-m-d') }}" class="{{ $input }}">
                        </div>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-zinc-700 mb-1">Catatan</label>
                        <input type="text" name="catatan" placeholder="Contoh: pelunasan, cicilan ke-2" class="{{ $input }}">
                    </div>
                    <div class="flex items-center gap-3 pt-2">
                        <button type="submit"
                                class="px-5 py-2.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-medium transition-colors shadow-xs cursor-pointer">
                            Simpan Pembayaran
                        </button>
                        <button type="button" @click="buka = false" class="px-3 py-2.5 text-sm font-medium text-zinc-600 hover:text-zinc-900 cursor-pointer">Batal</button>
                    </div>
                </form>
            </div>
            @endif
        </div>
    </div>
</x-app-layout>