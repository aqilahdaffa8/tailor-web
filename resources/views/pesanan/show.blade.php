<x-app-layout title="Detail Pesanan">
    <x-slot name="header">
        Detail Pesanan
    </x-slot>

    @php
        $dibatalkan = $pesanan->status === 'dibatalkan';
        $sisaHari = (int) today()->diffInDays($pesanan->deadline, false);
        $input = 'w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-lg text-sm focus:border-[#A8754F] focus:outline-none';
        $metode = ['tunai' => 'Tunai', 'transfer' => 'Transfer', 'qris' => 'QRIS'];
    @endphp

    <div class="mb-5 flex items-center justify-between gap-3">
        <a href="{{ route('pesanan.index') }}" class="inline-flex items-center gap-2 text-sm text-[#9A816B] hover:text-[#29302D] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar Pesanan
        </a>
    </div>

    {{-- Header --}}
    <div class="bg-white rounded-2xl border border-[#E7DED2] p-6 sm:p-8 mb-6">
        <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-5">
            <div>
                <div class="flex flex-wrap items-center gap-2.5">
                    <h2 class="font-heading text-xl font-bold text-[#29302D]">{{ $pesanan->nomor_pesanan }}</h2>
                    @include('pesanan._badge', ['status' => $pesanan->status])
                    @include('pesanan._badge', ['status' => $pesanan->status_pembayaran])
                </div>
                <p class="text-sm text-[#38443D] mt-2">
                    Pelanggan:
                    <a href="{{ route('pelanggan.show', $pesanan->pelanggan) }}" class="font-semibold text-[#A8754F] hover:underline">{{ $pesanan->pelanggan->nama }}</a>
                    <span class="text-[#9A816B]">&middot; {{ $pesanan->pelanggan->no_hp }}</span>
                </p>
                <div class="flex flex-wrap gap-x-6 gap-y-1 mt-3 text-xs text-[#9A816B]">
                    <span>Dipesan: <strong class="text-[#38443D]">{{ $pesanan->tanggal_pesan->format('d M Y') }}</strong></span>
                    <span>Deadline: <strong class="text-[#38443D]">{{ $pesanan->deadline->format('d M Y') }}</strong>
                        @if($pesanan->status === 'aktif')
                            @if($sisaHari < 0)
                                <span class="text-[#B86B67] font-semibold">(terlambat {{ abs($sisaHari) }} hari)</span>
                            @elseif($sisaHari <= 3)
                                <span class="text-[#C49A52] font-semibold">({{ $sisaHari === 0 ? 'hari ini' : 'H-'.$sisaHari }})</span>
                            @endif
                        @endif
                    </span>
                </div>
                @if($pesanan->catatan)
                    <p class="text-xs text-[#38443D] mt-3 bg-[#F7F4EE] rounded-lg px-3 py-2 border border-[#E7DED2]">{{ $pesanan->catatan }}</p>
                @endif
            </div>

            @unless($dibatalkan)
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('pesanan.edit', $pesanan) }}"
                       class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-[#E7DED2] text-xs font-semibold text-[#38443D] hover:bg-[#F7F4EE] transition-colors">
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
                                class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl border border-[#B86B67]/50 text-xs font-semibold text-[#B86B67] hover:bg-[#B86B67]/10 transition-colors cursor-pointer">
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

                <div class="bg-white rounded-2xl border border-[#E7DED2] overflow-hidden">
                    <div class="px-6 py-5 border-b border-[#E7DED2] bg-[#F7F4EE]/50 flex flex-wrap items-start justify-between gap-3">
                        <div>
                            <div class="flex items-center gap-2.5">
                                <h3 class="font-heading font-semibold text-[#29302D]">{{ $item->jenisJasa->nama_jasa ?? 'Item' }}</h3>
                                @include('pesanan._badge', ['status' => $item->status_item])
                            </div>
                            <p class="text-xs text-[#9A816B] mt-1">
                                {{ $item->detail_pekerjaan ?: 'Tanpa detail pekerjaan' }}
                                @if($item->polaDetail)
                                    &middot; Pola {{ $item->polaDetail->jenisPakaian->nama_jenis ?? '' }} {{ $item->polaDetail->size }}
                                @endif
                                @if($item->pengukuran)
                                    &middot; Ukuran {{ $item->pengukuran->tanggal_ukur->format('d M Y') }}
                                @endif
                            </p>
                            @if($item->catatan)
                                <p class="text-xs text-[#38443D] mt-1">Catatan: {{ $item->catatan }}</p>
                            @endif
                        </div>
                        <div class="text-right">
                            <p class="font-heading font-bold text-[#A8754F]">Rp {{ number_format((float) $item->harga, 0, ',', '.') }}</p>
                            @if($item->deadline)
                                <p class="text-[11px] text-[#9A816B]">Deadline {{ $item->deadline->format('d M Y') }}</p>
                            @endif
                        </div>
                    </div>

                    <div class="p-6 space-y-7">
                        {{-- Progress jahitan --}}
                        <div>
                            <div class="flex items-center justify-between mb-4">
                                <h4 class="text-xs font-semibold text-[#38443D] uppercase tracking-wider">Progress Pengerjaan ({{ $tuntas }}/{{ $tahapan->count() }})</h4>
                                @unless($dibatalkan)
                                    <div class="flex items-center gap-2">
                                        <form action="{{ route('pesanan.item.progress', $item) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="aksi" value="mundur">
                                            <button type="submit" @disabled($tuntas === 0)
                                                    class="px-3 py-1.5 rounded-lg border border-[#E7DED2] text-xs font-medium text-[#38443D] hover:bg-[#F7F4EE] transition-colors cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                                                Mundurkan
                                            </button>
                                        </form>
                                        <form action="{{ route('pesanan.item.progress', $item) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="aksi" value="maju">
                                            <button type="submit" @disabled(! $tahapanAktif)
                                                    class="px-3 py-1.5 rounded-lg bg-[#29302D] hover:bg-[#38443D] text-white text-xs font-semibold transition-all cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed">
                                                Selesaikan: {{ $tahapanAktif?->nama_tahapan ?? 'Semua tahap tuntas' }}
                                            </button>
                                        </form>
                                    </div>
                                @endunless
                            </div>

                            <ol class="space-y-0">
                                @foreach($tahapan as $tahap)
                                    @php $berjalan = $tahapanAktif && $tahapanAktif->id === $tahap->id; @endphp
                                    <li class="flex gap-3">
                                        <div class="flex flex-col items-center">
                                            <span class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 border-2
                                                {{ $tahap->is_completed ? 'bg-[#6F8A78] border-[#6F8A78] text-white' : ($berjalan ? 'border-[#C49A52] bg-[#C49A52]/10' : 'border-[#E7DED2] bg-white') }}">
                                                @if($tahap->is_completed)
                                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                                    </svg>
                                                @endif
                                            </span>
                                            @unless($loop->last)
                                                <span class="w-0 flex-1 min-h-5 border-l-2 border-dashed {{ $tahap->is_completed ? 'border-[#6F8A78]' : 'border-[#E7DED2]' }}"></span>
                                            @endunless
                                        </div>
                                        <div class="pb-4 -mt-0.5">
                                            <p class="text-sm {{ $tahap->is_completed ? 'text-[#38443D] font-medium' : ($berjalan ? 'text-[#A8754F] font-semibold' : 'text-[#9A816B]') }}">
                                                {{ $tahap->nama_tahapan }}
                                            </p>
                                            @if($tahap->completed_at)
                                                <p class="text-[11px] text-[#9A816B]">{{ $tahap->completed_at->format('d M Y, H:i') }}</p>
                                            @endif
                                        </div>
                                    </li>
                                @endforeach
                            </ol>
                        </div>

                        {{-- Pemakaian bahan --}}
                        <div>
                            <h4 class="text-xs font-semibold text-[#38443D] uppercase tracking-wider mb-3">Pemakaian Bahan</h4>

                            @forelse($item->pemakaianBahan as $pemakaian)
                                <div class="flex items-center justify-between py-2.5 border-b border-[#E7DED2] last:border-0">
                                    <div>
                                        <p class="text-sm font-medium text-[#29302D]">{{ $pemakaian->bahan->nama_bahan }}</p>
                                        <p class="text-[11px] text-[#9A816B]">
                                            {{ rtrim(rtrim(number_format((float) $pemakaian->jumlah, 2, ',', '.'), '0'), ',') }} {{ $pemakaian->bahan->satuan }}
                                            @if($pemakaian->catatan) &middot; {{ $pemakaian->catatan }} @endif
                                        </p>
                                    </div>
                                    @unless($dibatalkan)
                                        <form action="{{ route('pesanan.pemakaian.destroy', $pemakaian) }}" method="POST"
                                              onsubmit="return confirm('Hapus pemakaian bahan ini? Stok akan dikembalikan.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-xs text-[#B86B67] hover:underline cursor-pointer">Hapus</button>
                                        </form>
                                    @endunless
                                </div>
                            @empty
                                <p class="text-xs text-[#9A816B] mb-3">Belum ada bahan yang dipakai untuk item ini.</p>
                            @endforelse

                            @unless($dibatalkan)
                                <form action="{{ route('pesanan.item.bahan.store', $item) }}" method="POST"
                                      class="mt-3 grid grid-cols-1 sm:grid-cols-12 gap-2">
                                    @csrf
                                    <select name="bahan_id" required class="{{ $input }} sm:col-span-5">
                                        <option value="">-- Pilih Bahan --</option>
                                        @foreach($daftarBahan as $bahan)
                                            <option value="{{ $bahan->id }}">
                                                {{ $bahan->nama_bahan }} (stok {{ rtrim(rtrim(number_format((float) $bahan->stok, 2, ',', '.'), '0'), ',') }} {{ $bahan->satuan }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="number" name="jumlah" min="0.01" step="0.01" required placeholder="Jumlah" class="{{ $input }} sm:col-span-2">
                                    <input type="text" name="catatan" placeholder="Catatan (opsional)" class="{{ $input }} sm:col-span-3">
                                    <button type="submit"
                                            class="sm:col-span-2 px-3 py-2 rounded-lg bg-[#A8754F] hover:bg-[#8F6342] text-white text-xs font-semibold transition-all cursor-pointer">
                                        Catat
                                    </button>
                                </form>
                            @endunless
                        </div>

                        {{-- Foto referensi --}}
                        <div>
                            <h4 class="text-xs font-semibold text-[#38443D] uppercase tracking-wider mb-3">Foto Referensi</h4>

                            @if($item->fotoReferensi->isNotEmpty())
                                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 mb-3">
                                    @foreach($item->fotoReferensi as $foto)
                                        <div class="group relative rounded-xl overflow-hidden border border-[#E7DED2] bg-[#F7F4EE]">
                                            <a href="{{ asset('storage/'.$foto->path_foto) }}" target="_blank" rel="noopener">
                                                <img src="{{ asset('storage/'.$foto->path_foto) }}" alt="{{ $foto->keterangan ?: 'Foto referensi' }}"
                                                     class="w-full h-28 object-cover">
                                            </a>
                                            @if($foto->keterangan)
                                                <p class="px-2 py-1 text-[11px] text-[#38443D] truncate">{{ $foto->keterangan }}</p>
                                            @endif
                                            @unless($dibatalkan)
                                                <form action="{{ route('pesanan.foto.destroy', $foto) }}" method="POST"
                                                      class="absolute top-1.5 right-1.5 opacity-0 group-hover:opacity-100 transition-opacity"
                                                      onsubmit="return confirm('Hapus foto ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" aria-label="Hapus foto"
                                                            class="w-6 h-6 rounded-full bg-[#B86B67] text-white flex items-center justify-center cursor-pointer">
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
                                <p class="text-xs text-[#9A816B] mb-3">Belum ada foto referensi.</p>
                            @endif

                            @unless($dibatalkan)
                                <form action="{{ route('pesanan.item.foto.store', $item) }}" method="POST" enctype="multipart/form-data"
                                      class="grid grid-cols-1 sm:grid-cols-12 gap-2">
                                    @csrf
                                    <input type="file" name="foto" accept="image/jpeg,image/png,image/webp" required
                                           class="{{ $input }} sm:col-span-5 text-xs file:mr-3 file:px-2 file:py-1 file:rounded-md file:border-0 file:bg-[#E7DED2] file:text-[#38443D]">
                                    <input type="text" name="keterangan" placeholder="Keterangan (opsional)" class="{{ $input }} sm:col-span-5">
                                    <button type="submit"
                                            class="sm:col-span-2 px-3 py-2 rounded-lg border border-[#E7DED2] text-xs font-semibold text-[#38443D] hover:bg-[#F7F4EE] transition-colors cursor-pointer">
                                        Unggah
                                    </button>
                                </form>
                            @endunless
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Kolom pembayaran --}}
        <div class="space-y-6">
            <div class="bg-white rounded-2xl border border-[#E7DED2] p-6">
                <h3 class="font-heading font-semibold text-[#29302D] mb-4">Ringkasan Pembayaran</h3>
                <div class="rounded-xl bg-[#F7F4EE] border border-dashed border-[#9A816B]/50 p-4 space-y-2.5">
                    <div class="flex justify-between text-sm">
                        <span class="text-[#9A816B]">Total tagihan</span>
                        <span class="font-heading font-bold text-[#29302D]">Rp {{ number_format((float) $pesanan->total_harga, 0, ',', '.') }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-[#9A816B]">Sudah dibayar</span>
                        <span class="font-medium text-[#6F8A78]">Rp {{ number_format((float) $pesanan->total_dibayar, 0, ',', '.') }}</span>
                    </div>
                    <div class="border-t border-dashed border-[#9A816B]/50 pt-2.5 flex justify-between text-sm">
                        <span class="text-[#9A816B]">Sisa</span>
                        <span class="font-heading font-bold text-[#A8754F]">Rp {{ number_format((float) $pesanan->sisa_pembayaran, 0, ',', '.') }}</span>
                    </div>
                </div>

                <h4 class="text-xs font-semibold text-[#38443D] uppercase tracking-wider mt-6 mb-2">Riwayat Pembayaran</h4>
                @forelse($pesanan->pembayaran as $bayar)
                    <div class="flex items-center justify-between py-2.5 border-b border-[#E7DED2] last:border-0">
                        <div>
                            <p class="text-sm font-medium text-[#29302D]">Rp {{ number_format((float) $bayar->jumlah, 0, ',', '.') }}</p>
                            <p class="text-[11px] text-[#9A816B]">
                                {{ $metode[$bayar->metode] ?? ucfirst($bayar->metode) }} &middot; {{ $bayar->tanggal_bayar->format('d M Y') }}
                                @if($bayar->catatan) &middot; {{ $bayar->catatan }} @endif
                            </p>
                        </div>
                    </div>
                @empty
                    <p class="text-xs text-[#9A816B]">Belum ada pembayaran.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
