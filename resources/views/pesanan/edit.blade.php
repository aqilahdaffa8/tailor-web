<x-app-layout title="Edit Pesanan">
    <x-slot name="header">
        Edit Pesanan
    </x-slot>

    @php
        $input = 'w-full px-3 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none';
        $label = 'block text-[11px] font-semibold text-[#38443D] uppercase mb-1';
        $dibatalkan = $pesanan->status === 'dibatalkan';
        $pengukuranPelanggan = $daftarPengukuran->where('pelanggan_id', $pesanan->pelanggan_id);
    @endphp

    <div class="mb-5">
        <a href="{{ route('pesanan.show', $pesanan) }}" class="inline-flex items-center gap-2 text-sm text-[#9A816B] hover:text-[#29302D] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Detail Pesanan
        </a>
    </div>

    @if($dibatalkan)
        <div class="max-w-4xl mb-6 rounded-2xl bg-[#B86B67]/10 border border-[#B86B67]/40 p-4 text-sm text-[#29302D]">
            Pesanan ini sudah dibatalkan, sehingga datanya tidak dapat diubah lagi.
        </div>
    @endif

    <div class="max-w-4xl space-y-6">
        {{-- Header pesanan --}}
        <form action="{{ route('pesanan.update', $pesanan) }}" method="POST"
              class="bg-white rounded-2xl border border-[#E7DED2] p-6 sm:p-8 space-y-5">
            @csrf
            @method('PUT')

            <div>
                <h3 class="font-heading font-semibold text-[#29302D]">{{ $pesanan->nomor_pesanan }}</h3>
                <p class="text-xs text-[#9A816B] mt-0.5">Pelanggan: {{ $pesanan->pelanggan->nama }}</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="{{ $label }}">Tanggal Pesan *</label>
                    <input type="date" name="tanggal_pesan" required @disabled($dibatalkan)
                           value="{{ old('tanggal_pesan', $pesanan->tanggal_pesan->format('Y-m-d')) }}" class="{{ $input }}">
                </div>
                <div>
                    <label class="{{ $label }}">Deadline *</label>
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
                        class="px-5 py-2.5 rounded-xl bg-[#29302D] hover:bg-[#38443D] text-white text-sm font-semibold transition-all cursor-pointer">
                    Simpan Perubahan
                </button>
            @endunless
        </form>

        {{-- Item pesanan --}}
        <div class="space-y-4">
            <h3 class="font-heading font-semibold text-[#29302D]">Item Pesanan</h3>

            @foreach($pesanan->itemPesanan as $item)
                <div class="bg-white rounded-2xl border border-[#E7DED2] p-5 sm:p-6" x-data="{ buka: false }">
                    <div class="flex items-center justify-between gap-3">
                        <div>
                            <p class="font-heading font-semibold text-[#29302D]">{{ $item->jenisJasa->nama_jasa ?? 'Item' }}</p>
                            <p class="text-xs text-[#9A816B]">
                                {{ $item->detail_pekerjaan ?: 'Tanpa detail pekerjaan' }} &middot;
                                Rp {{ number_format((float) $item->harga, 0, ',', '.') }}
                            </p>
                        </div>
                        @unless($dibatalkan)
                            <button type="button" @click="buka = !buka"
                                    class="px-3 py-1.5 rounded-lg border border-[#E7DED2] text-xs font-medium text-[#38443D] hover:bg-[#F7F4EE] transition-colors cursor-pointer"
                                    x-text="buka ? 'Tutup' : 'Ubah'"></button>
                        @endunless
                    </div>

                    @unless($dibatalkan)
                        <div x-show="buka" x-cloak class="mt-5 pt-5 border-t border-[#E7DED2]">
                            <form action="{{ route('pesanan.item.update', $item) }}" method="POST" class="space-y-4">
                                @csrf
                                @method('PUT')
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <label class="{{ $label }}">Harga (Rp) *</label>
                                        <input type="number" min="0" step="1000" name="harga" required
                                               value="{{ (int) $item->harga }}" class="{{ $input }}">
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
                                <button type="submit"
                                        class="px-4 py-2 rounded-xl bg-[#29302D] hover:bg-[#38443D] text-white text-xs font-semibold transition-all cursor-pointer">
                                    Simpan Item
                                </button>
                            </form>

                            <form action="{{ route('pesanan.item.destroy', $item) }}" method="POST" class="mt-3"
                                  onsubmit="return confirm('Hapus item ini dari pesanan?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-xs text-[#B86B67] hover:underline cursor-pointer">Hapus item ini</button>
                            </form>
                        </div>
                    @endunless
                </div>
            @endforeach
        </div>

        {{-- Tambah item --}}
        @unless($dibatalkan)
            <form action="{{ route('pesanan.item.store', $pesanan) }}" method="POST"
                  class="bg-white rounded-2xl border border-dashed border-[#9A816B]/50 p-5 sm:p-6 space-y-4"
                  x-data="{ jasa: @js($daftarJasa->map(fn ($j) => ['id' => $j->id, 'harga' => (float) $j->harga_default])->values()), harga: '' }">
                @csrf
                <h3 class="font-heading font-semibold text-[#29302D]">Tambah Item Baru</h3>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="{{ $label }}">Jenis Jasa *</label>
                        <select name="jenis_jasa_id" required class="{{ $input }}"
                                @change="const j = jasa.find(x => x.id == $event.target.value); if (j) { harga = j.harga }">
                            <option value="">-- Pilih Jasa --</option>
                            @foreach($daftarJasa as $jasa)
                                <option value="{{ $jasa->id }}">{{ $jasa->nama_jasa }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="{{ $label }}">Harga (Rp) *</label>
                        <input type="number" min="0" step="1000" name="harga" required x-model="harga" class="{{ $input }}">
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
                        class="px-4 py-2.5 rounded-xl bg-[#A8754F] hover:bg-[#8F6342] text-white text-xs font-semibold transition-all cursor-pointer">
                    Tambah Item
                </button>
            </form>
        @endunless
    </div>
</x-app-layout>
