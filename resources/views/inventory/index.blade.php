<x-app-layout title="Inventory Bahan & Peralatan">
    <x-slot name="header">
        Manajemen Inventaris
    </x-slot>

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
            <div class="mb-6 p-4 rounded-2xl bg-[#C49A52]/10 border border-[#C49A52]/30 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-[#C49A52]/20 flex items-center justify-center text-[#A8754F] shrink-0">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-heading font-semibold text-xs text-[#29302D]">Peringatan Stok Bahan Menipis</p>
                        <p class="text-[11px] text-[#9A816B]">Terdapat {{ $bahanMenipisCount }} bahan yang stoknya telah mencapai atau di bawah batas minimum.</p>
                    </div>
                </div>
                <a href="{{ route('inventory.index', ['tab' => 'bahan', 'menipis' => 1]) }}"
                   class="px-3 py-1.5 rounded-lg bg-white border border-[#E7DED2] text-xs font-semibold text-[#A8754F] hover:bg-[#F7F4EE] transition-colors shrink-0">
                    Lihat Bahan Menipis
                </a>
            </div>
        @endif

        {{-- Navigasi Tab --}}
        <div class="flex items-center gap-2 mb-6 border-b border-[#E7DED2] pb-3">
            <button type="button" @click="tab = 'bahan'"
                    :class="tab === 'bahan' ? 'bg-[#29302D] text-white' : 'bg-white text-[#9A816B] hover:text-[#29302D] border border-[#E7DED2]'"
                    class="px-4 py-2 rounded-xl text-xs font-semibold transition-all cursor-pointer">
                Bahan & Kain ({{ $bahanList->count() }})
            </button>
            <button type="button" @click="tab = 'peralatan'"
                    :class="tab === 'peralatan' ? 'bg-[#29302D] text-white' : 'bg-white text-[#9A816B] hover:text-[#29302D] border border-[#E7DED2]'"
                    class="px-4 py-2 rounded-xl text-xs font-semibold transition-all cursor-pointer">
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
                           class="px-3.5 py-2 bg-white border border-[#E7DED2] rounded-xl text-xs focus:border-[#A8754F] focus:outline-none flex-1 min-w-[140px]">
                    <select name="kategori_id"
                            class="px-3 py-2 bg-white border border-[#E7DED2] rounded-xl text-xs focus:border-[#A8754F] focus:outline-none">
                        <option value="">Semua Kategori</option>
                        @foreach($kategoriBahanList as $kat)
                            <option value="{{ $kat->id }}" {{ $kategoriId == $kat->id ? 'selected' : '' }}>
                                {{ $kat->nama_kategori }}
                            </option>
                        @endforeach
                    </select>
                    <button type="submit"
                            class="px-3.5 py-2 bg-[#F7F4EE] border border-[#E7DED2] rounded-xl text-xs font-semibold text-[#38443D] hover:bg-[#E7DED2] transition-colors cursor-pointer">
                        Filter
                    </button>
                    @if($search || $kategoriId || $hanyaMenipis)
                        <a href="{{ route('inventory.index', ['tab' => 'bahan']) }}"
                           class="px-2.5 py-2 text-xs text-[#9A816B] hover:text-[#29302D] transition-colors">
                            Reset
                        </a>
                    @endif
                </form>

                <button type="button" @click="modalBahanOpen = true"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#29302D] hover:bg-[#38443D] text-white text-xs font-semibold transition-all shrink-0 cursor-pointer">
                    <svg class="w-4 h-4 text-[#C49A52]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Bahan Baru
                </button>
            </div>

            {{-- Tabel Bahan --}}
            <div class="bg-white rounded-2xl border border-[#E7DED2] overflow-hidden">
                @if($bahanList->isEmpty())
                    <div class="py-16 text-center">
                        <p class="font-heading font-semibold text-sm text-[#29302D]">Belum ada data bahan inventaris</p>
                        <p class="text-xs text-[#9A816B] mt-1">Daftarkan kain, benang, kancing, dan aksesoris lainnya.</p>
                    </div>
                @else
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-[#E7DED2] bg-[#F7F4EE]/60">
                                <th class="text-left px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider">Nama Bahan</th>
                                <th class="text-left px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider hidden sm:table-cell">Kategori</th>
                                <th class="text-left px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider">Stok Tersedia</th>
                                <th class="text-left px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider hidden md:table-cell">Batas Min</th>
                                <th class="text-center px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E7DED2]">
                            @foreach($bahanList as $bahan)
                                <tr class="hover:bg-[#F7F4EE]/50 transition-colors">
                                    <td class="px-6 py-4">
                                        <p class="font-medium text-[#29302D]">{{ $bahan->nama_bahan }}</p>
                                        <p class="text-xs text-[#9A816B] sm:hidden">{{ $bahan->kategoriBahan->nama_kategori ?? '-' }}</p>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-[#38443D] hidden sm:table-cell">
                                        <span class="px-2 py-0.5 rounded-md bg-[#F7F4EE] border border-[#E7DED2]">
                                            {{ $bahan->kategoriBahan->nama_kategori ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4">
                                        <span class="font-heading font-bold text-[#29302D]">
                                            {{ rtrim(rtrim(number_format($bahan->stok, 2, '.', ''), '0'), '.') }}
                                        </span>
                                        <span class="text-xs text-[#9A816B]">{{ $bahan->satuan }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-[#9A816B] hidden md:table-cell">
                                        {{ rtrim(rtrim(number_format($bahan->stok_minimum, 2, '.', ''), '0'), '.') }} {{ $bahan->satuan }}
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($bahan->isMenipis())
                                            <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#B86B67]/15 text-[#B86B67] border border-[#B86B67]/30">
                                                Menipis
                                            </span>
                                        @else
                                            <span class="px-2.5 py-1 rounded-full text-[11px] font-semibold bg-[#6F8A78]/15 text-[#6F8A78] border border-[#6F8A78]/30">
                                                Tersedia
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <div class="flex items-center justify-end gap-1.5">
                                            <button type="button"
                                                    @click="openStokMasuk({{ $bahan->id }}, '{{ addslashes($bahan->nama_bahan) }}', '{{ $bahan->satuan }}', {{ $bahan->stok }})"
                                                    class="px-2.5 py-1.5 rounded-lg bg-[#29302D] hover:bg-[#38443D] text-white text-xs font-semibold cursor-pointer transition-colors"
                                                    title="Tambah Stok Masuk">
                                                + Stok
                                            </button>
                                            <a href="{{ route('inventory.bahan.riwayat', $bahan) }}"
                                               class="p-1.5 rounded-lg border border-[#E7DED2] text-xs font-medium text-[#38443D] hover:bg-[#F7F4EE] transition-colors"
                                               title="Riwayat Mutasi Stok">
                                                <svg class="w-4 h-4 text-[#9A816B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                                </svg>
                                            </a>
                                            <form action="{{ route('inventory.bahan.destroy', $bahan) }}" method="POST"
                                                  onsubmit="return confirm('Hapus bahan {{ $bahan->nama_bahan }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="p-1.5 rounded-lg text-[#B86B67] hover:bg-[#B86B67]/10 transition-colors cursor-pointer"
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
                @endif
            </div>
        </div>

        {{-- TAB 2: PERALATAN & MESIN --}}
        <div x-show="tab === 'peralatan'" x-cloak>
            {{-- Toolbar Tambah & Filter --}}
            <div class="flex items-center justify-between gap-3 mb-6">
                <p class="text-xs text-[#9A816B]">Daftar aset mesin jahit, obras, gunting pola, setrika uap, dan alat kerja.</p>

                <button type="button" @click="modalAlatOpen = true"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#29302D] hover:bg-[#38443D] text-white text-xs font-semibold transition-all shrink-0 cursor-pointer ml-auto">
                    <svg class="w-4 h-4 text-[#C49A52]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Tambah Alat Baru
                </button>
            </div>

            {{-- Tabel Peralatan --}}
            <div class="bg-white rounded-2xl border border-[#E7DED2] overflow-hidden">
                @if($peralatanList->isEmpty())
                    <div class="py-16 text-center">
                        <p class="font-heading font-semibold text-sm text-[#29302D]">Belum ada data peralatan</p>
                        <p class="text-xs text-[#9A816B] mt-1">Daftarkan mesin dan perkakas pendukung atelier jahit Anda.</p>
                    </div>
                @else
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-[#E7DED2] bg-[#F7F4EE]/60">
                                <th class="text-left px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider">Nama Alat</th>
                                <th class="text-center px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider">Jumlah</th>
                                <th class="text-center px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider">Kondisi</th>
                                <th class="text-left px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider hidden sm:table-cell">Tanggal Beli</th>
                                <th class="text-left px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider hidden md:table-cell">Catatan</th>
                                <th class="px-6 py-3.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E7DED2]">
                            @foreach($peralatanList as $alat)
                                <tr class="hover:bg-[#F7F4EE]/50 transition-colors">
                                    <td class="px-6 py-4 font-medium text-[#29302D]">{{ $alat->nama_alat }}</td>
                                    <td class="px-6 py-4 text-center font-heading font-bold text-[#29302D]">{{ $alat->jumlah }} unit</td>
                                    <td class="px-6 py-4 text-center">
                                        @php
                                            $kondisiClass = [
                                                'Baik' => 'bg-[#6F8A78]/15 text-[#6F8A78] border-[#6F8A78]/30',
                                                'Rusak Ringan' => 'bg-[#C49A52]/15 text-[#A8754F] border-[#C49A52]/30',
                                                'Rusak Berat' => 'bg-[#B86B67]/15 text-[#B86B67] border-[#B86B67]/30',
                                                'Perlu Servis' => 'bg-[#B86B67]/15 text-[#B86B67] border-[#B86B67]/30',
                                            ][$alat->kondisi] ?? 'bg-[#F7F4EE] text-[#9A816B] border-[#E7DED2]';
                                        @endphp
                                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold border {{ $kondisiClass }}">
                                            {{ $alat->kondisi }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 text-xs text-[#9A816B] hidden sm:table-cell">
                                        {{ $alat->tanggal_beli ? $alat->tanggal_beli->format('d M Y') : '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-xs text-[#38443D] hidden md:table-cell">
                                        {{ $alat->catatan ?? '-' }}
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <form action="{{ route('inventory.peralatan.destroy', $alat) }}" method="POST"
                                              onsubmit="return confirm('Hapus alat {{ $alat->nama_alat }}?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit"
                                                    class="p-1.5 rounded-lg text-[#B86B67] hover:bg-[#B86B67]/10 transition-colors cursor-pointer"
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
                @endif
            </div>
        </div>

        {{-- MODAL TAMBAH BAHAN BARU --}}
        <div x-show="modalBahanOpen" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-black/40 flex items-center justify-center p-4">
            <div @click.away="modalBahanOpen = false"
                 class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full border border-[#E7DED2]">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-[#E7DED2]">
                    <h3 class="font-heading font-bold text-base text-[#29302D]">Tambah Bahan Baru</h3>
                    <button type="button" @click="modalBahanOpen = false" class="text-[#9A816B] hover:text-[#29302D] cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form action="{{ route('inventory.bahan.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
                            Kategori Bahan <span class="text-[#B86B67]">*</span>
                        </label>
                        <select name="kategori_bahan_id" required
                                class="w-full px-3 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($kategoriBahanList as $kat)
                                <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
                            Nama Bahan <span class="text-[#B86B67]">*</span>
                        </label>
                        <input type="text" name="nama_bahan" required placeholder="Contoh: Kain Katun Toyobo Putih"
                               class="w-full px-3 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
                                Satuan <span class="text-[#B86B67]">*</span>
                            </label>
                            <input type="text" name="satuan" required placeholder="Meter / Roll / Pcs"
                                   class="w-full px-3 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
                                Stok Awal <span class="text-[#B86B67]">*</span>
                            </label>
                            <input type="number" step="0.1" min="0" name="stok" required value="0"
                                   class="w-full px-3 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
                            Batas Stok Minimum (Peringatan) <span class="text-[#B86B67]">*</span>
                        </label>
                        <input type="number" step="0.1" min="0" name="stok_minimum" required value="5"
                               class="w-full px-3 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        <p class="text-[11px] text-[#9A816B] mt-1">Sistem akan memberi alert saat stok tersisa di bawah angka ini.</p>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#E7DED2]">
                        <button type="button" @click="modalBahanOpen = false"
                                class="px-4 py-2 rounded-xl text-xs font-semibold text-[#9A816B] hover:text-[#29302D] cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-[#29302D] hover:bg-[#38443D] text-white text-xs font-semibold cursor-pointer">
                            Simpan Bahan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL TAMBAH STOK MASUK --}}
        <div x-show="modalStokMasukOpen" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-black/40 flex items-center justify-center p-4">
            <div @click.away="modalStokMasukOpen = false"
                 class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full border border-[#E7DED2]">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-[#E7DED2]">
                    <div>
                        <h3 class="font-heading font-bold text-base text-[#29302D]">Tambah Stok Masuk</h3>
                        <p class="text-xs text-[#9A816B] mt-0.5" x-text="selectedBahan.nama"></p>
                    </div>
                    <button type="button" @click="modalStokMasukOpen = false" class="text-[#9A816B] hover:text-[#29302D] cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form :action="'{{ url('/inventory/bahan') }}/' + selectedBahan.id + '/stok-masuk'" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
                            Jumlah Stok Masuk (<span x-text="selectedBahan.satuan"></span>) <span class="text-[#B86B67]">*</span>
                        </label>
                        <input type="number" step="0.1" min="0.1" name="jumlah" required placeholder="Contoh: 15"
                               class="w-full px-3 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
                            Keterangan / Sumber Pembelian (Opsional)
                        </label>
                        <input type="text" name="keterangan" placeholder="Contoh: Beli di Toko Kain Sentosa"
                               class="w-full px-3 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#E7DED2]">
                        <button type="button" @click="modalStokMasukOpen = false"
                                class="px-4 py-2 rounded-xl text-xs font-semibold text-[#9A816B] hover:text-[#29302D] cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-[#29302D] hover:bg-[#38443D] text-white text-xs font-semibold cursor-pointer">
                            Tambah Stok
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- MODAL TAMBAH PERALATAN BARU --}}
        <div x-show="modalAlatOpen" x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-black/40 flex items-center justify-center p-4">
            <div @click.away="modalAlatOpen = false"
                 class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full border border-[#E7DED2]">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-[#E7DED2]">
                    <h3 class="font-heading font-bold text-base text-[#29302D]">Tambah Peralatan Kerja</h3>
                    <button type="button" @click="modalAlatOpen = false" class="text-[#9A816B] hover:text-[#29302D] cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form action="{{ route('inventory.peralatan.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
                            Nama Peralatan / Mesin <span class="text-[#B86B67]">*</span>
                        </label>
                        <input type="text" name="nama_alat" required placeholder="Contoh: Mesin Jahit Juki DDL-8700"
                               class="w-full px-3 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                    </div>

                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
                                Jumlah (Unit) <span class="text-[#B86B67]">*</span>
                            </label>
                            <input type="number" min="1" name="jumlah" required value="1"
                                   class="w-full px-3 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
                                Kondisi <span class="text-[#B86B67]">*</span>
                            </label>
                            <select name="kondisi" required
                                    class="w-full px-3 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                                <option value="Baik">Baik</option>
                                <option value="Rusak Ringan">Rusak Ringan</option>
                                <option value="Perlu Servis">Perlu Servis</option>
                                <option value="Rusak Berat">Rusak Berat</option>
                            </select>
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
                            Tanggal Pembelian (Opsional)
                        </label>
                        <input type="date" name="tanggal_beli"
                               class="w-full px-3 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
                            Catatan (Opsional)
                        </label>
                        <textarea name="catatan" rows="2" placeholder="Catatan servis / lokasi peletakan..."
                                  class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none resize-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#E7DED2]">
                        <button type="button" @click="modalAlatOpen = false"
                                class="px-4 py-2 rounded-xl text-xs font-semibold text-[#9A816B] hover:text-[#29302D] cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-[#29302D] hover:bg-[#38443D] text-white text-xs font-semibold cursor-pointer">
                            Simpan Peralatan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
