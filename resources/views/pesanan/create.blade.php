<x-app-layout title="Buat Pesanan Baru">
    <x-slot name="header">
        Buat Pesanan Baru
    </x-slot>

    @php
        $inputClass = 'w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 placeholder:text-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 transition-colors';

        $dataPelanggan = $daftarPelanggan->map(fn ($p) => ['id' => $p->id, 'nama' => $p->nama, 'no_hp' => $p->no_hp])->values();
        $dataJasa = $daftarJasa->map(fn ($j) => ['id' => $j->id, 'nama' => $j->nama_jasa, 'harga' => (float) $j->harga_default])->values();
        $dataPola = $daftarPola->map(fn ($p) => ['id' => $p->id, 'label' => ($p->jenisPakaian->nama_jenis ?? 'Pola').' - '.$p->size])->values();
        $dataPengukuran = $daftarPengukuran->map(fn ($u) => [
            'id' => $u->id,
            'pelanggan_id' => $u->pelanggan_id,
            'label' => 'Ukuran '.$u->tanggal_ukur->format('d M Y'),
        ])->values();

        $itemKosong = ['jenis_jasa_id' => '', 'pola_detail_id' => '', 'pengukuran_id' => '', 'harga' => '', 'deadline' => '', 'detail_pekerjaan' => '', 'catatan' => ''];
        $itemAwal = collect(old('items', [$itemKosong]))
            ->map(fn ($item) => array_merge($itemKosong, array_map(fn ($v) => $v ?? '', (array) $item)))
            ->values();

        $init = [
            'pelangganId' => old('pelanggan_id', $pelangganTerpilih ?? ''),
            'items' => $itemAwal,
            'dp' => old('dp', ''),
            'pelanggan' => $dataPelanggan,
            'jasa' => $dataJasa,
            'pola' => $dataPola,
            'pengukuran' => $dataPengukuran,
        ];
    @endphp

    <div class="mb-5">
        <a href="{{ route('pesanan.index') }}" class="inline-flex items-center gap-2 text-sm text-zinc-500 hover:text-zinc-900 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar Pesanan
        </a>
    </div>

    <form action="{{ route('pesanan.store') }}" method="POST" class="max-w-4xl space-y-6"
          x-data="{
              ...@js($init),
              cari: '',
              terbuka: false,
              init() {
                  const terpilih = this.pelanggan.find(p => p.id == this.pelangganId);
                  if (terpilih) { this.cari = terpilih.nama; }
              },
              get hasilCari() {
                  const kata = this.cari.toLowerCase();
                  return this.pelanggan.filter(p => p.nama.toLowerCase().includes(kata) || (p.no_hp || '').includes(kata)).slice(0, 8);
              },
              pilihPelanggan(p) {
                  this.pelangganId = p.id;
                  this.cari = p.nama;
                  this.terbuka = false;
                  this.items.forEach(item => {
                      if (item.pengukuran_id && !this.pengukuranPelanggan().some(u => u.id == item.pengukuran_id)) { item.pengukuran_id = ''; }
                  });
              },
              pengukuranPelanggan() {
                  return this.pengukuran.filter(u => u.pelanggan_id == this.pelangganId);
              },
              tambahItem() {
                  this.items.push({ jenis_jasa_id: '', pola_detail_id: '', pengukuran_id: '', harga: '', deadline: '', detail_pekerjaan: '', catatan: '' });
              },
              hapusItem(i) {
                  if (this.items.length > 1) { this.items.splice(i, 1); }
              },
              pilihJasa(item) {
                  const jasa = this.jasa.find(j => j.id == item.jenis_jasa_id);
                  if (jasa && (item.harga === '' || item.harga === null)) { item.harga = jasa.harga; }
              },
              get total() {
                  return this.items.reduce((jumlah, item) => jumlah + (parseFloat(item.harga) || 0), 0);
              },
              get sisa() {
                  return Math.max(this.total - (parseFloat(this.dp) || 0), 0);
              },
              rupiah(angka) {
                  return 'Rp ' + Math.round(angka).toLocaleString('id-ID');
              }
          }">
        @csrf

        {{-- Data Pesanan --}}
        <div class="bg-white rounded-2xl border border-zinc-200 p-6 sm:p-8 space-y-5 shadow-xs">
            <div>
                <h3 class="text-base font-semibold text-zinc-900">Data Pesanan</h3>
                <p class="text-xs text-zinc-500 mt-0.5">Nomor pesanan dibuat otomatis saat disimpan.</p>
            </div>

            <div class="relative" @click.outside="terbuka = false">
                <label class="block text-xs font-medium text-zinc-700 mb-1">
                    Pelanggan <span class="text-rose-500">*</span>
                </label>
                <input type="hidden" name="pelanggan_id" :value="pelangganId">
                <input type="text" x-model="cari" @focus="terbuka = true" @input="terbuka = true; pelangganId = ''"
                       placeholder="Ketik nama atau no. HP pelanggan..." autocomplete="off"
                       class="{{ $inputClass }} @error('pelanggan_id') border-rose-500 @enderror">
                <div x-show="terbuka && hasilCari.length" x-cloak
                     class="absolute z-10 mt-1 w-full bg-white border border-zinc-200 rounded-lg shadow-lg overflow-hidden">
                    <template x-for="p in hasilCari" :key="p.id">
                        <button type="button" @click="pilihPelanggan(p)"
                                class="w-full text-left px-4 py-2.5 text-sm hover:bg-zinc-50 transition-colors cursor-pointer flex items-center justify-between">
                            <span class="font-medium text-zinc-900" x-text="p.nama"></span>
                            <span class="text-xs text-zinc-400 tabular-nums" x-text="p.no_hp"></span>
                        </button>
                    </template>
                </div>
                <p class="text-xs text-zinc-500 mt-1.5">
                    Pelanggan belum terdaftar?
                    <a href="{{ route('pelanggan.create') }}" class="text-zinc-900 font-medium hover:underline">Tambah pelanggan baru</a>
                </p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-medium text-zinc-700 mb-1">
                        Tanggal Pesan <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="tanggal_pesan" value="{{ old('tanggal_pesan', date('Y-m-d')) }}" required
                           class="{{ $inputClass }} @error('tanggal_pesan') border-rose-500 @enderror">
                </div>
                <div>
                    <label class="block text-xs font-medium text-zinc-700 mb-1">
                        Deadline <span class="text-rose-500">*</span>
                    </label>
                    <input type="date" name="deadline" value="{{ old('deadline', date('Y-m-d', strtotime('+7 days'))) }}" required
                           class="{{ $inputClass }} @error('deadline') border-rose-500 @enderror">
                </div>
            </div>

            <div>
                <label class="block text-xs font-medium text-zinc-700 mb-1">Catatan Pesanan (Opsional)</label>
                <textarea name="catatan" rows="2" placeholder="Contoh: pelanggan minta dikabari saat sudah jadi"
                          class="{{ $inputClass }}">{{ old('catatan') }}</textarea>
            </div>
        </div>

        {{-- Item Pesanan --}}
        <div class="space-y-4">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-base font-semibold text-zinc-900">Item Pesanan</h3>
                    <p class="text-xs text-zinc-500 mt-0.5">Daftar item pakaian atau layanan yang dipesan.</p>
                </div>
                <button type="button" @click="tambahItem()"
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-200 bg-white text-xs font-medium text-zinc-700 hover:bg-zinc-50 transition-colors shadow-xs cursor-pointer">
                    <svg class="w-3.5 h-3.5 text-zinc-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Item
                </button>
            </div>

            <template x-for="(item, i) in items" :key="i">
                <div class="bg-white rounded-2xl border border-zinc-200 p-5 sm:p-6 space-y-4 shadow-xs">
                    <div class="flex items-center justify-between">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-700" x-text="'Item ' + (i + 1)"></span>
                        <button type="button" x-show="items.length > 1" @click="hapusItem(i)"
                                class="inline-flex items-center gap-1 text-xs font-medium text-rose-600 hover:text-rose-700 cursor-pointer">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Hapus
                        </button>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-zinc-700 mb-1">Jenis Jasa <span class="text-rose-500">*</span></label>
                            <select :name="'items[' + i + '][jenis_jasa_id]'" x-model="item.jenis_jasa_id" @change="pilihJasa(item)" required
                                    class="{{ $inputClass }}">
                                <option value="">-- Pilih Jasa --</option>
                                <template x-for="j in jasa" :key="j.id">
                                    <option :value="j.id" x-text="j.nama" :selected="item.jenis_jasa_id == j.id"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-700 mb-1">Harga (Rp) <span class="text-rose-500">*</span></label>
                            <input type="number" min="0" step="1000" :name="'items[' + i + '][harga]'" x-model="item.harga" required placeholder="0"
                                   class="{{ $inputClass }} tabular-nums">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-700 mb-1">Pola (Opsional)</label>
                            <select :name="'items[' + i + '][pola_detail_id]'" x-model="item.pola_detail_id" class="{{ $inputClass }}">
                                <option value="">-- Tanpa Pola --</option>
                                <template x-for="p in pola" :key="p.id">
                                    <option :value="p.id" x-text="p.label" :selected="item.pola_detail_id == p.id"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-700 mb-1">Data Ukuran Pelanggan (Opsional)</label>
                            <select :name="'items[' + i + '][pengukuran_id]'" x-model="item.pengukuran_id" class="{{ $inputClass }}">
                                <option value="" x-text="pelangganId ? (pengukuranPelanggan().length ? '-- Tanpa Ukuran --' : 'Pelanggan belum punya data ukuran') : 'Pilih pelanggan terlebih dahulu'"></option>
                                <template x-for="u in pengukuranPelanggan()" :key="u.id">
                                    <option :value="u.id" x-text="u.label" :selected="item.pengukuran_id == u.id"></option>
                                </template>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-700 mb-1">Deadline Item (Opsional)</label>
                            <input type="date" :name="'items[' + i + '][deadline]'" x-model="item.deadline" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-700 mb-1">Detail Pekerjaan</label>
                            <input type="text" :name="'items[' + i + '][detail_pekerjaan]'" x-model="item.detail_pekerjaan"
                                   placeholder="Contoh: Kemeja lengan panjang, kerah italia" class="{{ $inputClass }}">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-700 mb-1">Catatan Item</label>
                        <input type="text" :name="'items[' + i + '][catatan]'" x-model="item.catatan" class="{{ $inputClass }}">
                    </div>
                </div>
            </template>
        </div>

        {{-- Ringkasan & DP --}}
        <div class="bg-white rounded-2xl border border-zinc-200 p-6 sm:p-8 shadow-xs">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                <div class="space-y-4">
                    <div>
                        <h3 class="text-base font-semibold text-zinc-900">Uang Muka (DP) - Opsional</h3>
                        <p class="text-xs text-zinc-500 mt-0.5">Catat uang muka yang dibayar saat pemesanan.</p>
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-zinc-700 mb-1">Nominal DP (Rp)</label>
                        <input type="number" min="0" step="1000" name="dp" x-model="dp" placeholder="0" class="{{ $inputClass }} tabular-nums">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-zinc-700 mb-1">Metode Pembayaran</label>
                        <select name="metode_dp" class="{{ $inputClass }}">
                            @foreach(['tunai' => 'Tunai', 'transfer' => 'Transfer', 'qris' => 'QRIS'] as $nilai => $label)
                                <option value="{{ $nilai }}" @selected(old('metode_dp') === $nilai)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="rounded-xl bg-zinc-50 border border-zinc-200 p-5 space-y-3 self-start">
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-600">Total tagihan</span>
                        <span class="font-semibold text-zinc-900 tabular-nums" x-text="rupiah(total)"></span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-zinc-600">DP dibayar</span>
                        <span class="font-medium text-zinc-800 tabular-nums" x-text="rupiah(parseFloat(dp) || 0)"></span>
                    </div>
                    <div class="border-t border-zinc-200 pt-3 flex justify-between text-sm">
                        <span class="text-zinc-600 font-medium">Sisa pembayaran</span>
                        <span class="font-bold text-zinc-900 tabular-nums" x-text="rupiah(sisa)"></span>
                    </div>
                </div>
            </div>
        </div>

        <div class="flex items-center gap-3">
            <button type="submit"
                    class="inline-flex items-center gap-2 px-5 py-2.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-medium transition-colors shadow-xs cursor-pointer">
                <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Simpan Pesanan
            </button>
            <a href="{{ route('pesanan.index') }}" class="px-4 py-2.5 text-sm font-medium text-zinc-600 hover:text-zinc-900 transition-colors">Batal</a>
        </div>
    </form>
</x-app-layout>
