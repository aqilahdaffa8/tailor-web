<x-app-layout title="Pengaturan & Master Data">
    <x-slot name="header">
        Pengaturan Usaha & Master Data
    </x-slot>

    @php
        $inputClass = 'w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 placeholder:text-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 transition-colors';
    @endphp

    <div x-data="{ 
        activeTab: '{{ request('tab', 'jasa') }}', 
        modalJasaOpen: false, 
        isEditJasa: false,
        actionJasaUrl: '{{ route('pengaturan.jasa.store') }}',
        formJasa: {
            nama_jasa: '',
            harga_default: 0,
            is_active: true,
            deskripsi: '',
            tahapan: ['Menunggu Dikerjakan', 'Pengukuran', 'Pemotongan', 'Penjahitan', 'Finishing', 'Selesai', 'Diambil Pelanggan']
        },
        openTambahJasa() {
            this.isEditJasa = false;
            this.actionJasaUrl = '{{ route('pengaturan.jasa.store') }}';
            this.formJasa = {
                nama_jasa: '',
                harga_default: 0,
                is_active: true,
                deskripsi: '',
                tahapan: ['Menunggu Dikerjakan', 'Pengukuran', 'Pemotongan', 'Penjahitan', 'Finishing', 'Selesai', 'Diambil Pelanggan']
            };
            this.modalJasaOpen = true;
        },
        openEditJasa(jasaData) {
            this.isEditJasa = true;
            this.actionJasaUrl = '{{ url('pengaturan/jasa') }}/' + jasaData.id;
            this.formJasa = {
                nama_jasa: jasaData.nama_jasa || '',
                harga_default: jasaData.harga_default || 0,
                is_active: Boolean(jasaData.is_active),
                deskripsi: jasaData.deskripsi || '',
                tahapan: jasaData.template_tahapan && jasaData.template_tahapan.length > 0 
                    ? jasaData.template_tahapan.map(t => t.nama_tahapan) 
                    : ['Pengerjaan', 'Selesai']
            };
            this.modalJasaOpen = true;
        },
        addTahap() {
            this.formJasa.tahapan.push('');
        },
        removeTahap(index) {
            if (this.formJasa.tahapan.length > 1) {
                this.formJasa.tahapan.splice(index, 1);
            }
        },
        modalKategoriOpen: false,
        kategoriType: '',
        kategoriActionUrl: '',
        kategoriTitle: '',
        kategoriNama: '',
        openEditKategori(type, id, nama) {
            this.kategoriType = type;
            this.kategoriActionUrl = '{{ url('pengaturan') }}/kategori-' + type + '/' + id;
            this.kategoriNama = nama;
            if (type === 'pola') this.kategoriTitle = 'Edit Kategori Pola';
            else if (type === 'pengeluaran') this.kategoriTitle = 'Edit Kategori Pengeluaran';
            else if (type === 'tutorial') this.kategoriTitle = 'Edit Kategori Tutorial';
            else if (type === 'bahan') this.kategoriTitle = 'Edit Kategori Bahan';
            this.modalKategoriOpen = true;
        }
    }">
        <!-- Tab Navigation -->
        <div class="flex items-center gap-2 overflow-x-auto pb-4 mb-6 border-b border-zinc-200">
            <button type="button"
                    @click="activeTab = 'jasa'"
                    :class="activeTab === 'jasa' ? 'bg-zinc-900 text-white shadow-xs' : 'bg-white text-zinc-600 hover:text-zinc-900 border border-zinc-200'"
                    class="px-3.5 py-2 rounded-lg text-xs font-medium transition-colors cursor-pointer shrink-0">
                Jenis Jasa & Tahapan
            </button>
            <button type="button"
                    @click="activeTab = 'pola'"
                    :class="activeTab === 'pola' ? 'bg-zinc-900 text-white shadow-xs' : 'bg-white text-zinc-600 hover:text-zinc-900 border border-zinc-200'"
                    class="px-3.5 py-2 rounded-lg text-xs font-medium transition-colors cursor-pointer shrink-0">
                Kategori Pola
            </button>
            <button type="button"
                    @click="activeTab = 'pengeluaran'"
                    :class="activeTab === 'pengeluaran' ? 'bg-zinc-900 text-white shadow-xs' : 'bg-white text-zinc-600 hover:text-zinc-900 border border-zinc-200'"
                    class="px-3.5 py-2 rounded-lg text-xs font-medium transition-colors cursor-pointer shrink-0">
                Kategori Pengeluaran
            </button>
            <button type="button"
                    @click="activeTab = 'tutorial'"
                    :class="activeTab === 'tutorial' ? 'bg-zinc-900 text-white shadow-xs' : 'bg-white text-zinc-600 hover:text-zinc-900 border border-zinc-200'"
                    class="px-3.5 py-2 rounded-lg text-xs font-medium transition-colors cursor-pointer shrink-0">
                Kategori Tutorial
            </button>
            <button type="button"
                    @click="activeTab = 'profil'"
                    :class="activeTab === 'profil' ? 'bg-zinc-900 text-white shadow-xs' : 'bg-white text-zinc-600 hover:text-zinc-900 border border-zinc-200'"
                    class="px-3.5 py-2 rounded-lg text-xs font-medium transition-colors cursor-pointer shrink-0">
                Profil Usaha & Invoice
            </button>
        </div>

        <!-- TAB 1: JENIS JASA & TAHAPAN KERJA -->
        <div x-show="activeTab === 'jasa'" class="space-y-6">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="font-semibold text-base text-zinc-900">Daftar Jenis Jasa Jahit</h3>
                    <p class="text-xs text-zinc-500 mt-0.5">Setiap jenis jasa memiliki alur template tahapan pengerjaan tersendiri.</p>
                </div>
                <button type="button" 
                        @click="openTambahJasa()"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium transition-colors shadow-xs cursor-pointer">
                    <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Tambah Jasa Baru</span>
                </button>
            </div>

            <!-- List Jasa Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
                @foreach ($daftarJasa as $jasa)
                    <div class="bg-white rounded-2xl p-6 border border-zinc-200 shadow-xs relative flex flex-col justify-between">
                        <div>
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <h4 class="font-semibold text-base text-zinc-900">{{ $jasa->nama_jasa }}</h4>
                                        @if($jasa->is_active)
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">Aktif</span>
                                        @else
                                            <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-700 border border-zinc-200">Nonaktif</span>
                                        @endif
                                    </div>
                                    <p class="text-xs text-zinc-500 mt-1">{{ $jasa->deskripsi ?? 'Tidak ada deskripsi.' }}</p>
                                </div>
                                <span class="font-bold text-sm text-zinc-900 tabular-nums shrink-0">
                                    Rp {{ number_format($jasa->harga_default, 0, ',', '.') }}
                                </span>
                            </div>

                            <!-- Template Tahapan Visual -->
                            <div class="mt-5 pt-4 border-t border-zinc-200">
                                <p class="text-xs font-semibold uppercase tracking-wider text-zinc-700 mb-2.5">
                                    Alur Tahapan Pengerjaan ({{ $jasa->templateTahapan->count() }} Tahapan):
                                </p>
                                <div class="flex flex-wrap items-center gap-1.5">
                                    @foreach ($jasa->templateTahapan as $tahap)
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-zinc-50 border border-zinc-200 text-xs text-zinc-800">
                                            <span class="text-xs font-semibold text-zinc-500 tabular-nums">{{ $tahap->urutan }}.</span>
                                            <span>{{ $tahap->nama_tahapan }}</span>
                                        </span>
                                        @if(! $loop->last)
                                            <span class="text-zinc-400 text-xs">→</span>
                                        @endif
                                    @endforeach
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="mt-6 pt-3 flex items-center justify-end gap-2 border-t border-zinc-100">
                            <button type="button"
                                    @click="openEditJasa({{ $jasa->toJson() }})"
                                    class="px-3 py-1.5 rounded-lg text-xs font-medium text-zinc-700 hover:bg-zinc-100 transition-colors cursor-pointer">
                                Edit
                            </button>
                            <form action="{{ route('pengaturan.jasa.destroy', $jasa) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus jenis jasa ini?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-3 py-1.5 rounded-lg text-xs font-medium text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer">
                                    Hapus
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Modal Tambah/Edit Jasa -->
            <div x-show="modalJasaOpen" 
                 x-cloak 
                 class="fixed inset-0 z-50 overflow-y-auto bg-zinc-900/60 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl max-w-lg w-full p-6 sm:p-8 border border-zinc-200 shadow-xl relative" 
                     @click.away="modalJasaOpen = false">
                    <div class="flex items-center justify-between pb-4 border-b border-zinc-200">
                        <h3 class="font-semibold text-base text-zinc-900" x-text="isEditJasa ? 'Edit Jenis Jasa' : 'Tambah Jenis Jasa Baru'"></h3>
                        <button type="button" @click="modalJasaOpen = false" class="text-zinc-400 hover:text-zinc-600 cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form :action="actionJasaUrl" method="POST" class="mt-5 space-y-4">
                        @csrf
                        <template x-if="isEditJasa">
                            <input type="hidden" name="_method" value="PUT">
                        </template>

                        <div>
                            <label class="block text-xs font-medium text-zinc-700 mb-1">Nama Jasa <span class="text-rose-500">*</span></label>
                            <input type="text" name="nama_jasa" x-model="formJasa.nama_jasa" required placeholder="Contoh: Jahit Kebaya Modern" class="{{ $inputClass }}">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-zinc-700 mb-1">Harga Standar (Rp) <span class="text-rose-500">*</span></label>
                                <input type="number" name="harga_default" x-model="formJasa.harga_default" required min="0" class="{{ $inputClass }} tabular-nums">
                            </div>
                            <div class="flex items-center pt-5">
                                <label class="flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="is_active" value="1" x-model="formJasa.is_active" class="w-4 h-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900">
                                    <span class="text-xs text-zinc-700 font-medium">Status Aktif</span>
                                </label>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-zinc-700 mb-1">Deskripsi</label>
                            <textarea name="deskripsi" x-model="formJasa.deskripsi" rows="2" placeholder="Keterangan singkat jasa..." class="{{ $inputClass }} resize-none"></textarea>
                        </div>

                        <!-- Template Tahapan Input List -->
                        <div class="pt-3 border-t border-zinc-200">
                            <div class="flex items-center justify-between mb-2">
                                <label class="block text-xs font-medium text-zinc-700">Alur Tahapan Pengerjaan</label>
                                <button type="button" @click="addTahap()" class="text-xs font-medium text-zinc-900 hover:underline cursor-pointer">
                                    + Tambah Tahapan
                                </button>
                            </div>
                            <div class="space-y-2 max-h-48 overflow-y-auto pr-1">
                                <template x-for="(tahap, index) in formJasa.tahapan" :key="index">
                                    <div class="flex items-center gap-2">
                                        <span class="text-xs font-medium text-zinc-500 w-5 text-right tabular-nums" x-text="(index + 1) + '.'"></span>
                                        <input type="text" :name="'tahapan[' + index + ']'" x-model="formJasa.tahapan[index]" required class="flex-1 px-3 py-1.5 bg-white border border-zinc-200 rounded-lg text-xs text-zinc-900 focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900">
                                        <button type="button" @click="removeTahap(index)" class="text-rose-600 hover:text-rose-700 px-1 text-sm cursor-pointer">✕</button>
                                    </div>
                                </template>
                            </div>
                        </div>

                        <div class="pt-4 flex items-center justify-end gap-3 border-t border-zinc-200">
                            <button type="button" @click="modalJasaOpen = false" class="px-4 py-2 rounded-lg text-xs font-medium text-zinc-600 hover:text-zinc-900 cursor-pointer">Batal</button>
                            <button type="submit" class="px-5 py-2.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium shadow-xs cursor-pointer" x-text="isEditJasa ? 'Simpan Perubahan' : 'Simpan Jasa'"></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- TAB 2: KATEGORI POLA -->
        <div x-show="activeTab === 'pola'" class="space-y-6">
            <div class="bg-white rounded-2xl p-6 sm:p-8 border border-zinc-200 shadow-xs">
                <h3 class="font-semibold text-base text-zinc-900 mb-1">Kategori Pola Pakaian</h3>
                <p class="text-xs text-zinc-500 mb-6">Mengelompokkan jenis pakaian pada Library Pola (misal: Kemeja, Celana, Dress, Gamis).</p>

                <form action="{{ route('pengaturan.pola.store') }}" method="POST" class="flex gap-3 mb-6 max-w-xl">
                    @csrf
                    <input type="text" name="nama_kategori" required placeholder="Nama kategori pola baru..." class="{{ $inputClass }}">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium shadow-xs shrink-0 cursor-pointer">
                        + Tambah Kategori
                    </button>
                </form>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach ($kategoriPola as $pola)
                        <div class="flex items-center justify-between p-3.5 rounded-xl bg-zinc-50 border border-zinc-200">
                            <div>
                                <span class="font-medium text-sm text-zinc-900">{{ $pola->nama_kategori }}</span>
                                <span class="text-xs text-zinc-500 block">({{ $pola->jenis_pakaian_count }} jenis pakaian)</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button" 
                                        @click="openEditKategori('pola', {{ $pola->id }}, '{{ addslashes($pola->nama_kategori) }}')" 
                                        class="p-1 text-zinc-600 hover:text-zinc-900 hover:bg-zinc-200/60 rounded-lg transition-colors cursor-pointer" 
                                        title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </button>
                                <form action="{{ route('pengaturan.pola.destroy', $pola->id) }}" method="POST" onsubmit="return confirm('Hapus kategori ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer" title="Hapus">✕</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- TAB 3: KATEGORI PENGELUARAN -->
        <div x-show="activeTab === 'pengeluaran'" class="space-y-6">
            <div class="bg-white rounded-2xl p-6 sm:p-8 border border-zinc-200 shadow-xs">
                <h3 class="font-semibold text-base text-zinc-900 mb-1">Kategori Pengeluaran Keuangan</h3>
                <p class="text-xs text-zinc-500 mb-6">Pos biaya operasional penjahit (misal: Pembelian Bahan, Servis Mesin, Listrik).</p>

                <form action="{{ route('pengaturan.pengeluaran.store') }}" method="POST" class="flex gap-3 mb-6 max-w-xl">
                    @csrf
                    <input type="text" name="nama_kategori" required placeholder="Nama kategori pengeluaran..." class="{{ $inputClass }}">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium shadow-xs shrink-0 cursor-pointer">
                        + Tambah Kategori
                    </button>
                </form>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach ($kategoriPengeluaran as $peng)
                        <div class="flex items-center justify-between p-3.5 rounded-xl bg-zinc-50 border border-zinc-200">
                            <div>
                                <span class="font-medium text-sm text-zinc-900">{{ $peng->nama_kategori }}</span>
                                <span class="text-xs text-zinc-500 block">({{ $peng->pengeluaran_count }} transaksi)</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button" 
                                        @click="openEditKategori('pengeluaran', {{ $peng->id }}, '{{ addslashes($peng->nama_kategori) }}')" 
                                        class="p-1 text-zinc-600 hover:text-zinc-900 hover:bg-zinc-200/60 rounded-lg transition-colors cursor-pointer" 
                                        title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </button>
                                <form action="{{ route('pengaturan.pengeluaran.destroy', $peng->id) }}" method="POST" onsubmit="return confirm('Hapus kategori ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer" title="Hapus">✕</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- TAB 4: KATEGORI TUTORIAL -->
        <div x-show="activeTab === 'tutorial'" class="space-y-6">
            <div class="bg-white rounded-2xl p-6 sm:p-8 border border-zinc-200 shadow-xs">
                <h3 class="font-semibold text-base text-zinc-900 mb-1">Kategori Tutorial & Panduan</h3>
                <p class="text-xs text-zinc-500 mb-6">Mengelompokkan panduan jahit, video teknik, dan SOP di Library Tutorial.</p>

                <form action="{{ route('pengaturan.tutorial.store') }}" method="POST" class="flex gap-3 mb-6 max-w-xl">
                    @csrf
                    <input type="text" name="nama_kategori" required placeholder="Nama kategori tutorial baru..." class="{{ $inputClass }}">
                    <button type="submit" class="px-4 py-2 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium shadow-xs shrink-0 cursor-pointer">
                        + Tambah Kategori
                    </button>
                </form>

                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3">
                    @foreach ($kategoriTutorial as $tut)
                        <div class="flex items-center justify-between p-3.5 rounded-xl bg-zinc-50 border border-zinc-200">
                            <div>
                                <span class="font-medium text-sm text-zinc-900">{{ $tut->nama_kategori }}</span>
                                <span class="text-xs text-zinc-500 block">({{ $tut->tutorial_count }} tutorial)</span>
                            </div>
                            <div class="flex items-center gap-1">
                                <button type="button" 
                                        @click="openEditKategori('tutorial', {{ $tut->id }}, '{{ addslashes($tut->nama_kategori) }}')" 
                                        class="p-1 text-zinc-600 hover:text-zinc-900 hover:bg-zinc-200/60 rounded-lg transition-colors cursor-pointer" 
                                        title="Edit">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z"/>
                                    </svg>
                                </button>
                                <form action="{{ route('pengaturan.tutorial.destroy', $tut->id) }}" method="POST" onsubmit="return confirm('Hapus kategori ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 text-rose-600 hover:bg-rose-50 rounded-lg transition-colors cursor-pointer" title="Hapus">✕</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- TAB 5: PROFIL USAHA & INVOICE -->
        <div x-show="activeTab === 'profil'" class="space-y-6">
            <div class="bg-white rounded-2xl p-6 sm:p-8 border border-zinc-200 shadow-xs max-w-2xl">
                <h3 class="font-semibold text-base text-zinc-900 mb-1">Informasi Usaha & Format Invoice</h3>
                <p class="text-xs text-zinc-500 mb-6">Data ini dicantumkan pada cetak faktur/nota dan informasi kepada pelanggan.</p>

                <form action="{{ route('pengaturan.profil.update') }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-zinc-700 mb-1">Nama Usaha / Bisnis <span class="text-rose-500">*</span></label>
                            <input type="text" name="nama_usaha" required value="{{ old('nama_usaha', $profil['nama_usaha'] ?? '') }}" class="{{ $inputClass }}">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-700 mb-1">Nama Pemilik <span class="text-rose-500">*</span></label>
                            <input type="text" name="pemilik" required value="{{ old('pemilik', $profil['pemilik'] ?? '') }}" class="{{ $inputClass }}">
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-zinc-700 mb-1">No. WhatsApp / Telepon <span class="text-rose-500">*</span></label>
                            <input type="text" name="no_hp" required value="{{ old('no_hp', $profil['no_hp'] ?? '') }}" class="{{ $inputClass }} tabular-nums">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-700 mb-1">Prefix Nomor Nota / Invoice</label>
                            <input type="text" name="prefix_invoice" value="{{ old('prefix_invoice', $profil['prefix_invoice'] ?? 'ORD') }}" placeholder="Contoh: ORD" class="{{ $inputClass }}">
                        </div>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-700 mb-1">Alamat Lengkap Workshop</label>
                        <textarea name="alamat" rows="2" class="{{ $inputClass }}">{{ old('alamat', $profil['alamat'] ?? '') }}</textarea>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-700 mb-1">Catatan Kaki Invoice / Syarat Nota</label>
                        <textarea name="catatan_kaki_invoice" rows="2" class="{{ $inputClass }}">{{ old('catatan_kaki_invoice', $profil['catatan_kaki_invoice'] ?? '') }}</textarea>
                    </div>

                    <div class="pt-3">
                        <button type="submit" class="px-5 py-2.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium shadow-xs cursor-pointer">
                            Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Modal Edit Kategori Universal -->
        <div x-show="modalKategoriOpen" 
             x-cloak 
             class="fixed inset-0 z-50 overflow-y-auto bg-zinc-900/60 flex items-center justify-center p-4">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 border border-zinc-200 shadow-xl relative" 
                 @click.away="modalKategoriOpen = false">
                <div class="flex items-center justify-between pb-4 border-b border-zinc-200">
                    <h3 class="font-semibold text-base text-zinc-900" x-text="kategoriTitle"></h3>
                    <button type="button" @click="modalKategoriOpen = false" class="text-zinc-400 hover:text-zinc-600 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form :action="kategoriActionUrl" method="POST" class="mt-5 space-y-4">
                    @csrf
                    <input type="hidden" name="_method" value="PUT">

                    <div>
                        <label class="block text-xs font-medium text-zinc-700 mb-1">Nama Kategori <span class="text-rose-500">*</span></label>
                        <input type="text" name="nama_kategori" x-model="kategoriNama" required class="{{ $inputClass }}">
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-zinc-200">
                        <button type="button" @click="modalKategoriOpen = false" class="px-4 py-2 rounded-lg text-xs font-medium text-zinc-600 hover:text-zinc-900 cursor-pointer">Batal</button>
                        <button type="submit" class="px-5 py-2.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium shadow-xs cursor-pointer">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
