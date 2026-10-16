<x-app-layout title="Edit Pesanan">
    <x-slot name="header">
        Edit Pesanan
    </x-slot>

    @php
        $input = 'w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 placeholder:text-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 transition-colors disabled:bg-zinc-50 disabled:text-zinc-500';
        $label = 'block text-xs font-medium text-zinc-700 mb-1';
        $dibatalkan = $pesanan->status === 'dibatalkan';
        $pengukuranPelanggan = $daftarPengukuran->where('pelanggan_id', $pesanan->pelanggan_id);
    @endphp

    <div class="mb-5">
        <a href="{{ route('pesanan.show', $pesanan) }}" class="inline-flex items-center gap-2 text-sm text-zinc-500 hover:text-zinc-900 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Detail Pesanan
        </a>
    </div>

    @if($dibatalkan)
        <div class="max-w-4xl mb-6 rounded-2xl bg-rose-50 border border-rose-200 p-4 text-sm text-rose-800">
            Pesanan ini sudah dibatalkan, sehingga datanya tidak dapat diubah lagi.
        </div>
    @endif

    <div class="max-w-4xl space-y-6">
        {{-- Header pesanan --}}
        <form action="{{ route('pesanan.update', $pesanan) }}" method="POST"
              class="bg-white rounded-2xl border border-zinc-200 p-6 sm:p-8 space-y-5 shadow-xs">
            @csrf
            @method('PUT')

            <div>
                <h3 class="text-base font-semibold text-zinc-900">{{ $pesanan->nomor_pesanan }}</h3>
                <p class="text-xs text-zinc-500 mt-0.5">Pelanggan: <span class="font-medium text-zinc-900">{{ $pesanan->pelanggan->nama }}</span></p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="{{ $label }}">Tanggal Pesan <span class="text-rose-500">*</span></label>
                    <input type="date" name="tanggal_pesan" required @disabled($dibatalkan)
                           value="{{ old('tanggal_pesan', $pesanan->tanggal_pesan->format('Y-m-d')) }}" class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">Deadline <span class="text-rose-500">*</span></label>
                    <input type="date" name="deadline" required @disabled($dibatalkan)
                           value="{{ old('deadline', $pesanan->deadline->format('Y-m-d')) }}" class="{{ $input }}">
                </div>
            </div>

            <div>
                <label class="{{ $label }}">Catatan Pesanan</label>
                <textarea name="catatan" rows="2" @disabled($dibatalkan) class="{{ $input }}">{{ old('catatan', $pesanan->catatan) }}</textarea>
            </div>

            @unless($dibatalkan)
                <button type="submit"
                        class="px-5 py-2.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-medium transition-colors shadow-xs cursor-pointer">
                    Simpan Perubahan
                </button>
            @endunless
        </form>

        {{-- Item pesanan --}}
        <div class="space-y-4">
            <div>
                <h3 class="text-base font-semibold text-zinc-900">Item Pesanan</h3>
                <p class="text-xs text-zinc-500 mt-0.5">Kelola item pekerjaan dan rincian ukuran.</p>
            </div>

            @foreach($pesanan->itemPesanan as $item)
                <div class="bg-white rounded-2xl border border-zinc-200 p-5 sm:p-6 shadow-xs" x-data="{ buka: false }">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-semibold text-zinc-900">{{ $item->jenisJasa->nama_jasa ?? 'Item' }}</p>
                            <p class="text-xs text-zinc-500 mt-0.5">
                                {{ $item->detail_pekerjaan ?: 'Tanpa detail pekerjaan' }} &middot;
                                <span class="font-medium text-zinc-900 tabular-nums">Rp {{ number_format((float) $item->harga, 0, ',', '.') }}</span>
                            </p>
                        </div>
                        @unless($dibatalkan)
                            <button type="button" @click="buka = !buka"
                                    class="px-3 py-1.5 rounded-lg border border-zinc-200 bg-white text-xs font-medium text-zinc-700 hover:bg-zinc-50 transition-colors shadow-xs cursor-pointer"
                                    x-text="buka ? 'Tutup' : 'Ubah'"></button>
                        @endunless
                    </div>

                    @unless($dibatalkan)
                        <div x-show="buka" x-cloak class="mt-5 pt-5 border-t border-zinc-200">
                            <form action="{{ route('pesanan.item.update', $item) }}" method="POST" class="space-y-4">
                                @csrf
                                @method('PUT')
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="{{ $label }}">Harga (Rp) <span class="text-rose-500">*</span></label>
                                        <input type="number" min="0" step="1000" name="harga" required
                                               value="{{ (int) $item->harga }}" class="{{ $input }} tabular-nums">
                                    </div>
                                    <div>
                                        <label class="{{ $label }}">Deadline Item</label>
                                        <input type="date" name="deadline" value="{{ $item->deadline?->format('Y-m-d') }}" class="{{ $input }}">
                                    </div>
                                    <div>
                                        <label class="{{ $label }}">Pola</label>
                                        <select name="pola_detail_id" class="{{ $input }}">
                                            <option value="">-- Tanpa Pola --</option>
                                            @foreach($daftarPola as $pola)
                                                <option value="{{ $pola->id }}" @selected($item->pola_detail_id === $pola->id)>
                                                    {{ ($pola->jenisPakaian->nama_jenis ?? 'Pola').' - '.$pola->size }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div>
                                        <label class="{{ $label }}">Data Ukuran</label>
                                        <select name="pengukuran_id" class="{{ $input }}">
                                            <option value="">-- Tanpa Ukuran --</option>
                                            @foreach($pengukuranPelanggan as $ukuran)
                                                <option value="{{ $ukuran->id }}" @selected($item->pengukuran_id === $ukuran->id)>
                                                    Ukuran {{ $ukuran->tanggal_ukur->format('d M Y') }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div>
                                    <label class="{{ $label }}">Detail Pekerjaan</label>
                                    <input type="text" name="detail_pekerjaan" value="{{ $item->detail_pekerjaan }}" class="{{ $input }}">
                                </div>
                                <div>
                                    <label class="{{ $label }}">Catatan Item</label>
                                    <input type="text" name="catatan" value="{{ $item->catatan }}" class="{{ $input }}">
                                </div>
                                <div class="flex items-center gap-3 pt-1">
                                    <button type="submit"
                                            class="px-4 py-2 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium transition-colors shadow-xs cursor-pointer">
                                        Simpan Item
                                    </button>
                                </div>
                            </form>

                            <form action="{{ route('pesanan.item.destroy', $item) }}" method="POST" class="mt-3 pt-3 border-t border-zinc-100"
                                  onsubmit="return confirm('Hapus item ini dari pesanan?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs font-medium text-rose-600 hover:text-rose-700 cursor-pointer">Hapus item ini</button>
                            </form>
                        </div>
                    @endunless
                </div>
            @endforeach
        </div>

        {{-- Tambah item --}}
        @unless($dibatalkan)
            <form action="{{ route('pesanan.item.store', $pesanan) }}" method="POST"
                  class="bg-white rounded-2xl border border-zinc-200 p-5 sm:p-6 space-y-4 shadow-xs"
                  x-data="{ jasa: @js($daftarJasa->map(fn ($j) => ['id' => $j->id, 'harga' => (float) $j->harga_default])->values()), harga: '' }">
                @csrf
                <div>
                    <h3 class="text-base font-semibold text-zinc-900">Tambah Item Baru</h3>
                    <p class="text-xs text-zinc-500 mt-0.5">Tambahkan item jahitan baru ke dalam pesanan ini.</p>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="{{ $label }}">Jenis Jasa <span class="text-rose-500">*</span></label>
                        <select name="jenis_jasa_id" required class="{{ $input }}"
                                @change="const j = jasa.find(x => x.id == $event.target.value); if (j) { harga = j.harga }">
                            <option value="">-- Pilih Jasa --</option>
                            @foreach($daftarJasa as $jasa)
                                <option value="{{ $jasa->id }}">{{ $jasa->nama_jasa }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $label }}">Harga (Rp) <span class="text-rose-500">*</span></label>
                        <input type="number" min="0" step="1000" name="harga" required x-model="harga" class="{{ $input }} tabular-nums">
                    </div>
                    <div>
                        <label class="{{ $label }}">Pola</label>
                        <select name="pola_detail_id" class="{{ $input }}">
                            <option value="">-- Tanpa Pola --</option>
                            @foreach($daftarPola as $pola)
                                <option value="{{ $pola->id }}">{{ ($pola->jenisPakaian->nama_jenis ?? 'Pola').' - '.$pola->size }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $label }}">Data Ukuran</label>
                        <select name="pengukuran_id" class="{{ $input }}">
                            <option value="">-- Tanpa Ukuran --</option>
                            @foreach($pengukuranPelanggan as $ukuran)
                                <option value="{{ $ukuran->id }}">Ukuran {{ $ukuran->tanggal_ukur->format('d M Y') }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $label }}">Deadline Item</label>
                        <input type="date" name="deadline" class="{{ $input }}">
                    </div>
                    <div>
                        <label class="{{ $label }}">Detail Pekerjaan</label>
                        <input type="text" name="detail_pekerjaan" class="{{ $input }}">
                    </div>
                </div>

                <button type="submit"
                        class="px-4 py-2 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium transition-colors shadow-xs cursor-pointer">
                    Tambah Item
                </button>
            </form>
        @endunless
    </div>
</x-app-layout>
