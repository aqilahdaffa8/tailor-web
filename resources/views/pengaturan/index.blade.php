<x-app-layout title="Pengaturan & Master Data">
    <x-slot name="header">
        Pengaturan Atelier & Master Data
    </x-slot>

    <div x-data="{ activeTab: '{{ request('tab', 'jasa') }}', modalJasaOpen: false, editJasa: null }">
        <!-- Tab Navigation (Atelier Style) -->
        <div class="flex items-center gap-2 overflow-x-auto pb-4 mb-6 border-b border-[#E7DED2]">
            <button type="button"
                    @click="activeTab = 'jasa'"
                    :class="activeTab === 'jasa' ? 'bg-[#29302D] text-white shadow-xs' : 'bg-white text-[#9A816B] hover:text-[#29302D] border border-[#E7DED2]'"
                    class="px-4 py-2 rounded-xl text-xs font-semibold uppercase tracking-wider transition-all duration-200 cursor-pointer shrink-0">
                Jenis Jasa & Tahapan
            </button>
            <button type="button"
                    @click="activeTab = 'pola'"
                    :class="activeTab === 'pola' ? 'bg-[#29302D] text-white shadow-xs' : 'bg-white text-[#9A816B] hover:text-[#29302D] border border-[#E7DED2]'"
                    class="px-4 py-2 rounded-xl text-xs font-semibold uppercase tracking-wider transition-all duration-200 cursor-pointer shrink-0">
                Kategori Pola
            </button>
            <button type="button"
                    @click="activeTab = 'bahan'"
                    :class="activeTab === 'bahan' ? 'bg-[#29302D] text-white shadow-xs' : 'bg-white text-[#9A816B] hover:text-[#29302D] border border-[#E7DED2]'"
                    class="px-4 py-2 rounded-xl text-xs font-semibold uppercase tracking-wider transition-all duration-200 cursor-pointer shrink-0">
                Kategori Bahan
            </button>
            <button type="button"
                    @click="activeTab = 'pengeluaran'"
                    :class="activeTab === 'pengeluaran' ? 'bg-[#29302D] text-white shadow-xs' : 'bg-white text-[#9A816B] hover:text-[#29302D] border border-[#E7DED2]'"
                    class="px-4 py-2 rounded-xl text-xs font-semibold uppercase tracking-wider transition-all duration-200 cursor-pointer shrink-0">
                Kategori Pengeluaran
            </button>
            <button type="button"
                    @click="activeTab = 'profil'"
                    :class="activeTab === 'profil' ? 'bg-[#29302D] text-white shadow-xs' : 'bg-white text-[#9A816B] hover:text-[#29302D] border border-[#E7DED2]'"
                    class="px-4 py-2 rounded-xl text-xs font-semibold uppercase tracking-wider transition-all duration-200 cursor-pointer shrink-0">
                Profil Usaha & Invoice
            </button>
        </div>

        <!-- TAB 1: JENIS JASA & TAHAPAN KERJA -->
        <div x-show="activeTab === 'jasa'" class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-heading font-bold text-lg text-[#29302D]">Daftar Jenis Jasa Jahit</h3>
                    <p class="text-xs text-[#9A816B]">Setiap jenis jasa memiliki alur template tahapan pengerjaan tersendiri.</p>
                </div>
                <button type="button" 
                        @click="modalJasaOpen = true"
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#29302D] hover:bg-[#38443D] text-white text-xs font-semibold transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-[#C49A52]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Tambah Jasa Baru</span>
                </button>
            </div>

            <!-- List Jasa Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                @foreach ($daftarJasa as $jasa)
                    <div class="bg-white rounded-2xl p-6 border border-[#E7DED2] relative flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-heading font-bold text-base text-[#29302D]">{{ $jasa->nama_jasa }}</h4>
                                        @if($jasa->is_active)
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#6F8A78]/15 text-[#6F8A78]">Aktif</span>
                                        @else
                                            <span class="px-2 py-0.5 rounded-full text-[10px] font-semibold bg-[#9A816B]/15 text-[#9A816B]">Nonaktif</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-[#9A816B] mt-1">{{ $jasa->deskripsi ?? 'Tidak ada deskripsi.' }}</p>
                                </div>
                                <span class="font-heading font-bold text-sm text-[#A8754F] shrink-0">
                                    Rp {{ number_format($jasa->harga_default, 0, ',', '.') }}
                                </span>
                            </div>

                            <!-- Template Tahapan Visual -->
                            <div class="mt-5 pt-4 border-t border-dashed border-[#E7DED2]">
                                <p class="text-[11px] font-semibold uppercase tracking-wider text-[#38443D] mb-2.5">
                                    Alur Tahapan Pengerjaan ({{ $jasa->templateTahapan->count() }} Tahapan):
                                </p>
                                <div class="flex flex-wrap items-center gap-1.5">
                                    @foreach ($jasa->templateTahapan as $tahap)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-[#F7F4EE] border border-[#E7DED2] text-xs text-[#29302D]">
                                            <span class="text-[10px] font-bold text-[#A8754F]">{{ $tahap->urutan }}.</span>
                                            <span>{{ $tahap->nama_tahapan }}</span>
                                        </span>
                                        @if(! $loop->last)
                                            <span class="text-[#9A816B] text-xs">→</span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-6 pt-3 flex items-center justify-end gap-2 border-t border-[#E7DED2]/60">
                            <form action="{{ route('pengaturan.jasa.destroy', $jasa) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jenis jasa ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-medium text-[#B86B67] hover:bg-[#B86B67]/10 transition-colors cursor-pointer">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Modal Tambah Jasa Baru -->
            <div x-show="modalJasaOpen" 
                 x-cloak 
                 class="fixed inset-0 z-50 overflow-y-auto bg-black/40 flex items-center justify-center p-4">
                <div class="bg-white rounded-3xl max-w-lg w-full p-6 sm:p-8 border border-[#E7DED2] relative" 
                     @click.away="modalJasaOpen = false">
                    <div class="flex items-center justify-between pb-4 border-b border-[#E7DED2]">
                        <h3 class="font-heading font-bold text-lg text-[#29302D]">Tambah Jenis Jasa Baru</h3>
                        <button type="button" @click="modalJasaOpen = false" class="text-[#9A816B] hover:text-[#29302D]">
                            ✕
                        </button>
                    </div>

                    <form action="{{ route('pengaturan.jasa.store') }}" method="POST" class="mt-5 space-y-4" x-data="{
                        tahapanList: ['Menunggu Dikerjakan', 'Pengukuran', 'Pemotongan', 'Penjahitan', 'Finishing', 'Selesai', 'Diambil Pelanggan'],
                        addTahap() { this.tahapanList.push(''); },
                        removeTahap(index) { if (this.tahapanList.length > 1) this.tahapanList.splice(index, 1); }
                    }">
                        @csrf
                        <div>
                            <label class="block text-xs font-semibold text-[#38443D] uppercase">Nama Jasa *</label>
                            <input type="text" name="nama_jasa" required placeholder="Contoh: Jahit Kebaya Modern" class="mt-1 block w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-[#38443D] uppercase">Harga Standar (Rp) *</label>
                                <input type="number" name="harga_default" required min="0" value="0" class="mt-1 block w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                            </div>
                            <div class="flex items-center pt-5">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded border-[#E7DED2] text-[#A8754F]">
                                    <span class="text-xs text-[#29302D]">Status Aktif</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#38443D] uppercase">Deskripsi</label>
                            <textarea name="deskripsi" rows="2" placeholder="Keterangan singkat jasa..." class="mt-1 block w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none"></textarea>
                        </div>

                        <!-- Template Tahapan Input List -->
                        <div class="pt-2 border-t border-dashed border-[#E7DED2]">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-semibold text-[#38443D] uppercase">Alur Tahapan Pengerjaan</label>
                                <button type="button" @click="addTahap()" class="text-xs font-semibold text-[#A8754F] hover:underline cursor-pointer">
                                    + Tambah Tahapan
                                </button>
                            </div>
                            <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                <template x-for="(tahap, index) in tahapanList" :key="index">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-bold text-[#A8754F] w-5 text-right" x-text="(index + 1) + '.'"></span>
                                        <input type="text" :name="'tahapan[' + index + ']'" x-model="tahapanList[index]" required class="flex-1 px-3 py-1.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-lg text-xs focus:border-[#A8754F] focus:outline-none">
                                        <button type="button" @click="removeTahap(index)" class="text-[#B86B67] hover:text-red-700 px-1 text-sm cursor-pointer">✕</button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="pt-4 flex items-center justify-end gap-3 border-t border-[#E7DED2]">
                            <button type="button" @click="modalJasaOpen = false" class="px-4 py-2 rounded-xl border border-[#E7DED2] text-xs font-semibold text-[#9A816B]">Batal</button>
                            <button type="submit" class="px-5 py-2 rounded-xl bg-[#29302D] text-white text-xs font-semibold hover:bg-[#38443D]">Simpan Jasa</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- TAB 2: KATEGORI POLA -->
        <div x-show="activeTab === 'pola'" class="space-y-6">
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#E7DED2]">
                <h3 class="font-heading font-bold text-lg text-[#29302D] mb-1">Kategori Pola Pakaian</h3>
                <p class="text-xs text-[#9A816B] mb-6">Mengelompokkan jenis pakaian pada Library Pola (misal: Kemeja, Celana, Dress, Gamis).</p>

                <form action="{{ route('pengaturan.pola.store') }}" method="POST" class="flex gap-3 mb-6">
                    @csrf
                    <input type="text" name="nama_kategori" required placeholder="Nama kategori pola baru..." class="flex-1 px-4 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#29302D] hover:bg-[#38443D] text-white text-xs font-semibold cursor-pointer">
                        + Tambah Kategori
                    </button>
                </form>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach ($kategoriPola as $pola)
                        <div class="flex items-center justify-between p-3.5 rounded-xl bg-[#F7F4EE]/60 border border-[#E7DED2]">
                            <div>
                                <span class="font-medium text-sm text-[#29302D]">{{ $pola->nama_kategori }}</span>
                                <span class="text-[11px] text-[#9A816B] block">({{ $pola->jenis_pakaian_count }} jenis pakaian)</span>
                            </div>
                            <form action="{{ route('pengaturan.pola.destroy', $pola->id) }}" method="POST" onsubmit="return confirm('Hapus kategori ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1 text-[#B86B67] hover:bg-white rounded-lg transition-colors cursor-pointer">✕</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- TAB 3: KATEGORI BAHAN -->
        <div x-show="activeTab === 'bahan'" class="space-y-6">
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#E7DED2]">
                <h3 class="font-heading font-bold text-lg text-[#29302D] mb-1">Kategori Bahan Inventory</h3>
                <p class="text-xs text-[#9A816B] mb-6">Kategori bahan habis pakai yang dikelola di gudang internal (misal: Kain, Benang, Kancing, Resleting).</p>

                <form action="{{ route('pengaturan.bahan.store') }}" method="POST" class="flex gap-3 mb-6">
                    @csrf
                    <input type="text" name="nama_kategori" required placeholder="Nama kategori bahan baru..." class="flex-1 px-4 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#29302D] hover:bg-[#38443D] text-white text-xs font-semibold cursor-pointer">
                        + Tambah Kategori
                    </button>
                </form>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach ($kategoriBahan as $bahan)
                        <div class="flex items-center justify-between p-3.5 rounded-xl bg-[#F7F4EE]/60 border border-[#E7DED2]">
                            <div>
                                <span class="font-medium text-sm text-[#29302D]">{{ $bahan->nama_kategori }}</span>
                                <span class="text-[11px] text-[#9A816B] block">({{ $bahan->bahan_count }} item bahan)</span>
                            </div>
                            <form action="{{ route('pengaturan.bahan.destroy', $bahan->id) }}" method="POST" onsubmit="return confirm('Hapus kategori ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1 text-[#B86B67] hover:bg-white rounded-lg transition-colors cursor-pointer">✕</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- TAB 4: KATEGORI PENGELUARAN -->
        <div x-show="activeTab === 'pengeluaran'" class="space-y-6">
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#E7DED2]">
                <h3 class="font-heading font-bold text-lg text-[#29302D] mb-1">Kategori Pengeluaran Keuangan</h3>
                <p class="text-xs text-[#9A816B] mb-6">Pos biaya operasional penjahit (misal: Pembelian Bahan, Servis Mesin, Listrik).</p>

                <form action="{{ route('pengaturan.pengeluaran.store') }}" method="POST" class="flex gap-3 mb-6">
                    @csrf
                    <input type="text" name="nama_kategori" required placeholder="Nama kategori pengeluaran..." class="flex-1 px-4 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-[#29302D] hover:bg-[#38443D] text-white text-xs font-semibold cursor-pointer">
                        + Tambah Kategori
                    </button>
                </form>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach ($kategoriPengeluaran as $peng)
                        <div class="flex items-center justify-between p-3.5 rounded-xl bg-[#F7F4EE]/60 border border-[#E7DED2]">
                            <div>
                                <span class="font-medium text-sm text-[#29302D]">{{ $peng->nama_kategori }}</span>
                                <span class="text-[11px] text-[#9A816B] block">({{ $peng->pengeluaran_count }} transaksi)</span>
                            </div>
                            <form action="{{ route('pengaturan.pengeluaran.destroy', $peng->id) }}" method="POST" onsubmit="return confirm('Hapus kategori ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1 text-[#B86B67] hover:bg-white rounded-lg transition-colors cursor-pointer">✕</button>
                            </form>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- TAB 5: PROFIL USAHA & INVOICE -->
        <div x-show="activeTab === 'profil'" class="space-y-6">
            <div class="bg-white rounded-3xl p-6 sm:p-8 border border-[#E7DED2] max-w-2xl">
                <h3 class="font-heading font-bold text-lg text-[#29302D] mb-1">Informasi Usaha & Format Invoice</h3>
                <p class="text-xs text-[#9A816B] mb-6">Data ini dicantumkan pada cetak faktur/nota dan informasi kepada pelanggan.</p>

                <form action="{{ route('pengaturan.profil.update') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#38443D] uppercase">Nama Usaha / Atelier *</label>
                            <input type="text" name="nama_usaha" required value="{{ old('nama_usaha', $profil['nama_usaha'] ?? '') }}" class="mt-1 block w-full px-3 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#38443D] uppercase">Nama Pemilik *</label>
                            <input type="text" name="pemilik" required value="{{ old('pemilik', $profil['pemilik'] ?? '') }}" class="mt-1 block w-full px-3 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-semibold text-[#38443D] uppercase">No. WhatsApp / Telepon *</label>
                            <input type="text" name="no_hp" required value="{{ old('no_hp', $profil['no_hp'] ?? '') }}" class="mt-1 block w-full px-3 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs font-semibold text-[#38443D] uppercase">Prefix Nomor Nota / Invoice</label>
                            <input type="text" name="prefix_invoice" value="{{ old('prefix_invoice', $profil['prefix_invoice'] ?? 'ORD') }}" placeholder="Contoh: ORD" class="mt-1 block w-full px-3 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#38443D] uppercase">Alamat Lengkap Workshop</label>
                        <textarea name="alamat" rows="2" class="mt-1 block w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">{{ old('alamat', $profil['alamat'] ?? '') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#38443D] uppercase">Catatan Kaki Invoice / Syarat Nota</label>
                        <textarea name="catatan_kaki_invoice" rows="2" class="mt-1 block w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">{{ old('catatan_kaki_invoice', $profil['catatan_kaki_invoice'] ?? '') }}</textarea>
                    </div>

                    <div class="pt-3">
                        <button type="submit" class="px-6 py-2.5 rounded-xl bg-[#29302D] hover:bg-[#38443D] text-white text-xs font-semibold cursor-pointer">
                            Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
