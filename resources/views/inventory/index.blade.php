<x-app-layout title="Inventory Bahan & Peralatan">
    <x-slot name="header">
        Manajemen Inventaris
    </x-slot>

    @php
        $inputClass = 'w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 placeholder:text-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 transition-colors';
    @endphp

    <div x-data="{
        tab: '{{ $activeTab }}',
        modalBahanOpen: false,
        modalStokMasukOpen: false,
        modalAlatOpen: false,
        selectedBahan: { id: null, nama: '', satuan: '', stok: 0 },
        openStokMasuk(id, nama, satuan, stok) {
            this.selectedBahan = { id, nama, satuan, stok };
            this.modalStokMasukOpen = true;
        }
    }">
        {{-- Banner Alert Stok Menipis --}}
        @if($bahanMenipisCount > 0 && !$hanyaMenipis)
            <div class="mb-6 p-4 rounded-2xl bg-amber-50 border border-amber-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3 shadow-xs">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-amber-100 flex items-center justify-center text-amber-700 shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-xs text-amber-900">Peringatan Stok Bahan Menipis</p>
                        <p class="text-xs text-amber-800 mt-0.5">Terdapat {{ $bahanMenipisCount }} bahan yang stoknya telah mencapai atau di bawah batas minimum.</p>
                    </div>
                </div>
                <a href="{{ route('inventory.index', ['tab' => 'bahan', 'menipis' => 1]) }}"
                   class="px-3 py-1.5 rounded-lg bg-white border border-amber-200 text-xs font-medium text-amber-900 hover:bg-amber-100/60 transition-colors shadow-xs shrink-0">
                    Lihat Bahan Menipis
                </a>
            </div>
        @endif

        {{-- Navigasi Tab --}}
        <div class="flex items-center gap-2 mb-6 border-b border-zinc-200 pb-3">
            <button type="button" @click="tab = 'bahan'"
                    :class="tab === 'bahan' ? 'bg-zinc-900 text-white' : 'bg-white text-zinc-600 hover:text-zinc-900 border border-zinc-200'"
                    class="px-3.5 py-2 rounded-lg text-xs font-medium transition-colors shadow-xs cursor-pointer">
                Bahan & Kain ({{ $bahanList->count() }})
            </button>
            <button type="button" @click="tab = 'peralatan'"
                    :class="tab === 'peralatan' ? 'bg-zinc-900 text-white' : 'bg-white text-zinc-600 hover:text-zinc-900 border border-zinc-200'"
                    class="px-3.5 py-2 rounded-lg text-xs font-medium transition-colors shadow-xs cursor-pointer">
                Peralatan & Mesin ({{ $peralatanList->count() }})
            </button>
        </div>

        {{-- TAB 1: BAHAN & KAIN --}}
        <div x-show="tab === 'bahan'">
            {{-- Toolbar Filter & Tambah --}}
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
                <form method="GET" action="{{ route('inventory.index') }}" class="flex flex-wrap items-center gap-2 flex-1 max-w-lg">
                    <input type="hidden" name="tab" value="bahan">
                    <input type="text" name="search" value="{{ $search }}"
                           placeholder="Cari nama bahan..."
                           class="px-3 py-2 bg-white border border-zinc-200 rounded-lg text-xs text-zinc-900 placeholder:text-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 flex-1 min-w-[140px]">
                    <select name="kategori_id"
                            class="px-3 py-2 bg-white border border-zinc-200 rounded-lg text-xs text-zinc-900 focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900">
                        <option value="">Semua Kategori</option>
                        @foreach($kategoriBahanList as $kat)
                            <option value="{{ $kat->id }}" {{ $kategoriId == $kat->id ? 'selected' : '' }}>
                                {{ $kat->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit"
                            class="px-3.5 py-2 bg-white border border-zinc-200 rounded-lg text-xs font-medium text-zinc-700 hover:bg-zinc-50 transition-colors shadow-xs cursor-pointer">
                        Filter
                    </button>
                    @if($search || $kategoriId || $hanyaMenipis)
                        <a href="{{ route('inventory.index', ['tab' => 'bahan']) }}"
                           class="px-2.5 py-2 text-xs font-medium text-zinc-500 hover:text-zinc-900 transition-colors">
                            Reset
                        </a>
                    @endif
                </form>

                <button type="button" @click="modalBahanOpen = true"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium transition-colors shadow-xs shrink-0 cursor-pointer">
                    <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Bahan Baru
                </button>
            </div>

            {{-- Tabel Bahan --}}
            <div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden shadow-xs">
                @if($bahanList->isEmpty())
                    <div class="py-16 text-center">
                        <p class="font-semibold text-sm text-zinc-900">Belum ada data bahan inventaris</p>
                        <p class="text-xs text-zinc-500 mt-1">Daftarkan kain, benang, kancing, dan aksesoris lainnya.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm table-mobile-10">
                        <thead>
                            <tr class="border-b border-zinc-200 bg-zinc-50 text-xs font-semibold text-zinc-700 uppercase tracking-wider">
                                <th class="text-left px-6 py-3.5">Nama Bahan</th>
                                <th class="text-left px-6 py-3.5 hidden sm:table-cell">Kategori</th>
                                <th class="text-left px-6 py-3.5">Stok Tersedia</th>
                                <th class="text-left px-6 py-3.5 hidden md:table-cell">Batas Min</th>
                                <th class="text-center px-6 py-3.5">Status</th>
                                <th class="px-6 py-3.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @foreach($bahanList as $bahan)
                                <tr class="hover:bg-zinc-50/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-zinc-900">{{ $bahan->nama_bahan }}</p>
                                        <p class="text-xs text-zinc-500 sm:hidden">{{ $bahan->kategoriBahan->nama_kategori ?? '-' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-zinc-700 hidden sm:table-cell">
                                        <span class="px-2 py-0.5 rounded-md bg-zinc-100 border border-zinc-200 font-medium">
                                            {{ $bahan->kategoriBahan->nama_kategori ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="font-bold text-zinc-900 tabular-nums">
                                            {{ rtrim(rtrim(number_format($bahan->stok, 2, '.', ''), '0'), '.') }}
                                        </span>
                                        <span class="text-xs text-zinc-500">{{ $bahan->satuan }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-zinc-500 tabular-nums hidden md:table-cell">
                                        {{ rtrim(rtrim(number_format($bahan->stok_minimum, 2, '.', ''), '0'), '.') }} {{ $bahan->satuan }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($bahan->isMenipis())
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200/60">
                                                Menipis
                                            </span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                                Tersedia
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button type="button"
                                                    @click="openStokMasuk({{ $bahan->id }}, '{{ addslashes($bahan->nama_bahan) }}', '{{ $bahan->satuan }}', {{ $bahan->stok }})"
                                                    class="px-2.5 py-1.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium cursor-pointer transition-colors shadow-xs"
                                                    title="Tambah Stok Masuk">
                                                + Stok
                                            </button>
                                            <a href="{{ route('inventory.bahan.riwayat', $bahan) }}"
                                               class="p-1.5 rounded-lg border border-zinc-200 text-zinc-600 hover:bg-zinc-50 transition-colors shadow-xs"
                                               title="Riwayat Mutasi Stok">
                                                <svg class="w-4 h-4 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </a>
                                            <form action="{{ route('inventory.bahan.destroy', $bahan) }}" method="POST"
                                                  onsubmit="return confirm('Hapus bahan {{ $bahan->nama_bahan }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                                        title="Hapus Bahan">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

        {{-- TAB 2: PERALATAN & MESIN --}}
        <div x-show="tab === 'peralatan'" x-cloak>
            {{-- Toolbar Tambah & Filter --}}
            <div class="flex items-center justify-between gap-3 mb-6">
                <p class="text-xs text-zinc-500">Daftar aset mesin jahit, obras, gunting pola, setrika uap, dan alat kerja.</p>

                <button type="button" @click="modalAlatOpen = true"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium transition-colors shadow-xs shrink-0 cursor-pointer ml-auto">
                    <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Alat Baru
                </button>
            </div>

            {{-- Tabel Peralatan --}}
            <div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden shadow-xs">
                @if($peralatanList->isEmpty())
                    <div class="py-16 text-center">
                        <p class="font-semibold text-sm text-zinc-900">Belum ada data peralatan</p>
                        <p class="text-xs text-zinc-500 mt-1">Daftarkan mesin dan perkakas pendukung usaha jahit Anda.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm table-mobile-10">
                        <thead>
                            <tr class="border-b border-zinc-200 bg-zinc-50 text-xs font-semibold text-zinc-700 uppercase tracking-wider">
                                <th class="text-left px-6 py-3.5">Nama Alat</th>
                                <th class="text-center px-6 py-3.5">Jumlah</th>
                                <th class="text-center px-6 py-3.5">Kondisi</th>
                                <th class="text-left px-6 py-3.5 hidden sm:table-cell">Tanggal Beli</th>
                                <th class="text-left px-6 py-3.5 hidden md:table-cell">Catatan</th>
                                <th class="px-6 py-3.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-zinc-200">
                            @foreach($peralatanList as $alat)
                                <tr class="hover:bg-zinc-50/50 transition-colors">
                                    <td class="px-6 py-4 font-medium text-zinc-900">{{ $alat->nama_alat }}</td>
                                    <td class="px-6 py-4 text-center font-bold text-zinc-900 tabular-nums">{{ $alat->jumlah }} unit</td>
                                    <td class="px-6 py-4 text-center">
                                        @php
                                            $kondisiClass = [
                                                'Baik' => 'bg-emerald-50 text-emerald-700 border-emerald-200/60',
                                                'Rusak Ringan' => 'bg-amber-50 text-amber-700 border-amber-200/60',
                                                'Rusak Berat' => 'bg-rose-50 text-rose-700 border-rose-200/60',
                                                'Perlu Servis' => 'bg-rose-50 text-rose-700 border-rose-200/60',
                                            ][$alat->kondisi] ?? 'bg-zinc-100 text-zinc-700 border-zinc-200';
                                        @endphp
                                        <span class="px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $kondisiClass }}">
                                            {{ $alat->kondisi }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-zinc-500 tabular-nums hidden sm:table-cell">
                                        {{ $alat->tanggal_beli ? $alat->tanggal_beli->format('d M Y') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-xs text-zinc-500 hidden md:table-cell">
                                        {{ $alat->catatan ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <form action="{{ route('inventory.peralatan.destroy', $alat) }}" method="POST"
                                              onsubmit="return confirm('Hapus alat {{ $alat->nama_alat }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                                    title="Hapus Alat">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>
        </div>

        {{-- MODAL TAMBAH BAHAN BARU --}}
        <div x-show="modalBahanOpen" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-zinc-900/60 flex items-center justify-center p-4">
            <div @click.away="modalBahanOpen = false"
                 class="bg-white rounded-2xl p-6 sm:p-8 max-w-md w-full border border-zinc-200 shadow-xl">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-zinc-200">
                    <h3 class="font-semibold text-base text-zinc-900">Tambah Bahan Baru</h3>
                    <button type="button" @click="modalBahanOpen = false" class="text-zinc-400 hover:text-zinc-600 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form action="{{ route('inventory.bahan.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-zinc-700 mb-1">
                            Kategori Bahan <span class="text-rose-500">*</span>
                        </label>
                        <select name="kategori_bahan_id" required
                                class="{{ $inputClass }}">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($kategoriBahanList as $kat)
                                <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-700 mb-1">
                            Nama Bahan <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama_bahan" required placeholder="Contoh: Kain Katun Toyobo Putih"
                               class="{{ $inputClass }}">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-zinc-700 mb-1">
                                Satuan <span class="text-rose-500">*</span>
                            </label>
                            <input type="text" name="satuan" required placeholder="Meter / Roll / Pcs"
                                   class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-700 mb-1">
                                Stok Awal <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" step="0.1" min="0" name="stok" required value="0"
                                   class="{{ $inputClass }} tabular-nums">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-700 mb-1">
                            Batas Stok Minimum (Peringatan) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" step="0.1" min="0" name="stok_minimum" required value="5"
                               class="{{ $inputClass }} tabular-nums">
                        <p class="text-xs text-zinc-500 mt-1">Sistem akan memberi alert saat stok tersisa di bawah angka ini.</p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200">
                        <button type="button" @click="modalBahanOpen = false"
                                class="px-4 py-2 rounded-lg text-xs font-medium text-zinc-600 hover:text-zinc-900 cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium shadow-xs cursor-pointer">
                            Simpan Bahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL TAMBAH STOK MASUK --}}
        <div x-show="modalStokMasukOpen" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-zinc-900/60 flex items-center justify-center p-4">
            <div @click.away="modalStokMasukOpen = false"
                 class="bg-white rounded-2xl p-6 sm:p-8 max-w-md w-full border border-zinc-200 shadow-xl">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-zinc-200">
                    <div>
                        <h3 class="font-semibold text-base text-zinc-900">Tambah Stok Masuk</h3>
                        <p class="text-xs text-zinc-500 mt-0.5" x-text="selectedBahan.nama"></p>
                    </div>
                    <button type="button" @click="modalStokMasukOpen = false" class="text-zinc-400 hover:text-zinc-600 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form :action="'{{ url('/inventory/bahan') }}/' + selectedBahan.id + '/stok-masuk'" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-zinc-700 mb-1">
                            Jumlah Stok Masuk (<span x-text="selectedBahan.satuan"></span>) <span class="text-rose-500">*</span>
                        </label>
                        <input type="number" step="0.1" min="0.1" name="jumlah" required placeholder="Contoh: 15"
                               class="{{ $inputClass }} tabular-nums">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-700 mb-1">
                            Keterangan / Sumber Pembelian (Opsional)
                        </label>
                        <input type="text" name="keterangan" placeholder="Contoh: Beli di Toko Kain Sentosa"
                               class="{{ $inputClass }}">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200">
                        <button type="button" @click="modalStokMasukOpen = false"
                                class="px-4 py-2 rounded-lg text-xs font-medium text-zinc-600 hover:text-zinc-900 cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium shadow-xs cursor-pointer">
                            Tambah Stok
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL TAMBAH PERALATAN BARU --}}
        <div x-show="modalAlatOpen" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-zinc-900/60 flex items-center justify-center p-4">
            <div @click.away="modalAlatOpen = false"
                 class="bg-white rounded-2xl p-6 sm:p-8 max-w-md w-full border border-zinc-200 shadow-xl">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-zinc-200">
                    <h3 class="font-semibold text-base text-zinc-900">Tambah Peralatan Kerja</h3>
                    <button type="button" @click="modalAlatOpen = false" class="text-zinc-400 hover:text-zinc-600 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form action="{{ route('inventory.peralatan.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-zinc-700 mb-1">
                            Nama Peralatan / Mesin <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama_alat" required placeholder="Contoh: Mesin Jahit Juki DDL-8700"
                               class="{{ $inputClass }}">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-zinc-700 mb-1">
                                Jumlah (Unit) <span class="text-rose-500">*</span>
                            </label>
                            <input type="number" min="1" name="jumlah" required value="1"
                                   class="{{ $inputClass }} tabular-nums">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-700 mb-1">
                                Kondisi <span class="text-rose-500">*</span>
                            </label>
                            <select name="kondisi" required
                                    class="{{ $inputClass }}">
                                <option value="Baik">Baik</option>
                                <option value="Rusak Ringan">Rusak Ringan</option>
                                <option value="Perlu Servis">Perlu Servis</option>
                                <option value="Rusak Berat">Rusak Berat</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-700 mb-1">
                            Tanggal Pembelian (Opsional)
                        </label>
                        <input type="date" name="tanggal_beli"
                               class="{{ $inputClass }} tabular-nums">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-700 mb-1">
                            Catatan (Opsional)
                        </label>
                        <textarea name="catatan" rows="2" placeholder="Catatan servis / lokasi peletakan..."
                                  class="{{ $inputClass }} resize-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200">
                        <button type="button" @click="modalAlatOpen = false"
                                class="px-4 py-2 rounded-lg text-xs font-medium text-zinc-600 hover:text-zinc-900 cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium shadow-xs cursor-pointer">
                            Simpan Peralatan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
