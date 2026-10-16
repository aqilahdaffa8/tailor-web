@php
    $rp = fn ($angka) => 'Rp '.number_format((float) $angka, 0, ',', '.');
    $tabUrl = fn (string $t) => route('keuangan.index', ['tab' => $t, 'dari' => $dari, 'sampai' => $sampai]);
    $selisihPositif = $ringkasan['selisih'] >= 0;
    $hariIni = now()->toDateString();
@endphp

<x-app-layout title="Keuangan">
    <x-slot name="header">
        Keuangan Usaha
    </x-slot>

    <div x-data="{
        modalOpen: false,
        edit: null,
        form: { kategori: '', nominal: '', tanggal: '{{ $hariIni }}', catatan: '' },
        bukaTambah() {
            this.edit = null;
            this.form = { kategori: '', nominal: '', tanggal: '{{ $hariIni }}', catatan: '' };
            this.modalOpen = true;
        },
        bukaUbah(d) {
            this.edit = d.id;
            this.form = { kategori: d.kategori ?? '', nominal: d.nominal, tanggal: d.tanggal, catatan: d.catatan ?? '' };
            this.modalOpen = true;
        }
    }">
        {{-- Filter Periode --}}
        <form method="GET" action="{{ route('keuangan.index') }}"
              class="bg-white rounded-2xl border border-zinc-200 p-4 mb-6 flex flex-wrap items-end gap-3 shadow-xs">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div>
                <label class="block text-xs font-medium text-zinc-700 mb-1">Dari Tanggal</label>
                <input type="date" name="dari" value="{{ $dari }}"
                       class="px-3 py-2 bg-white border border-zinc-200 rounded-lg text-xs text-zinc-900 focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 tabular-nums">
            </div>
            <div>
                <label class="block text-xs font-medium text-zinc-700 mb-1">Sampai Tanggal</label>
                <input type="date" name="sampai" value="{{ $sampai }}"
                       class="px-3 py-2 bg-white border border-zinc-200 rounded-lg text-xs text-zinc-900 focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 tabular-nums">
            </div>
            @if($tab === 'pengeluaran')
                <div>
                    <label class="block text-xs font-medium text-zinc-700 mb-1">Kategori</label>
                    <select name="kategori_id"
                            class="px-3 py-2 bg-white border border-zinc-200 rounded-lg text-xs text-zinc-900 focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900">
                        <option value="">Semua Kategori</option>
                        @foreach($kategoriList as $kat)
                            <option value="{{ $kat->id }}" {{ $kategoriId === $kat->id ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <button type="submit"
                    class="px-4 py-2 bg-zinc-900 hover:bg-zinc-800 text-white rounded-lg text-xs font-medium transition-colors shadow-xs cursor-pointer">
                Terapkan
            </button>
            <a href="{{ route('keuangan.index', ['tab' => $tab]) }}"
               class="px-2.5 py-2 text-xs font-medium text-zinc-500 hover:text-zinc-900 transition-colors">
                Bulan Ini
            </a>
            <p class="ml-auto text-xs text-zinc-500">Periode: <span class="font-medium text-zinc-700">{{ $ringkasan['periode']['label'] }}</span></p>
        </form>

        {{-- Navigasi Tab --}}
        <div class="flex items-center gap-2 mb-6 border-b border-zinc-200 pb-3">
            <a href="{{ $tabUrl('ringkasan') }}"
               class="px-3.5 py-2 rounded-lg text-xs font-medium transition-colors shadow-xs {{ $tab === 'ringkasan' ? 'bg-zinc-900 text-white' : 'bg-white text-zinc-600 hover:text-zinc-900 border border-zinc-200' }}">
                Ringkasan
            </a>
            <a href="{{ $tabUrl('pendapatan') }}"
               class="px-3.5 py-2 rounded-lg text-xs font-medium transition-colors shadow-xs {{ $tab === 'pendapatan' ? 'bg-zinc-900 text-white' : 'bg-white text-zinc-600 hover:text-zinc-900 border border-zinc-200' }}">
                Pendapatan ({{ $ringkasan['jumlah_pembayaran'] }})
            </a>
            <a href="{{ $tabUrl('pengeluaran') }}"
               class="px-3.5 py-2 rounded-lg text-xs font-medium transition-colors shadow-xs {{ $tab === 'pengeluaran' ? 'bg-zinc-900 text-white' : 'bg-white text-zinc-600 hover:text-zinc-900 border border-zinc-200' }}">
                Pengeluaran ({{ $ringkasan['jumlah_pengeluaran'] }})
            </a>
        </div>

        {{-- TAB RINGKASAN --}}
        @if($tab === 'ringkasan')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-2xl border border-zinc-200 p-5 shadow-xs">
                    <p class="text-xs font-medium text-zinc-500">Pendapatan</p>
                    <p class="font-bold text-2xl text-zinc-900 tabular-nums mt-1">{{ $rp($ringkasan['pendapatan']) }}</p>
                    <p class="text-xs text-zinc-500 mt-1 tabular-nums">{{ $ringkasan['jumlah_pembayaran'] }} pembayaran masuk</p>
                </div>
                <div class="bg-white rounded-2xl border border-zinc-200 p-5 shadow-xs">
                    <p class="text-xs font-medium text-zinc-500">Pengeluaran</p>
                    <p class="font-bold text-2xl text-rose-600 tabular-nums mt-1">{{ $rp($ringkasan['pengeluaran']) }}</p>
                    <p class="text-xs text-zinc-500 mt-1 tabular-nums">{{ $ringkasan['jumlah_pengeluaran'] }} catatan pengeluaran</p>
                </div>
                <div class="bg-white rounded-2xl border border-zinc-200 p-5 shadow-xs">
                    <p class="text-xs font-medium text-zinc-500">Selisih (Pendapatan - Pengeluaran)</p>
                    <p class="font-bold text-2xl mt-1 tabular-nums {{ $selisihPositif ? 'text-zinc-900' : 'text-rose-600' }}">
                        {{ $selisihPositif ? '' : '-' }}{{ $rp(abs($ringkasan['selisih'])) }}
                    </p>
                    <p class="text-xs text-zinc-500 mt-1">{{ $selisihPositif ? 'Surplus pada periode ini' : 'Defisit pada periode ini' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden shadow-xs">
                    <div class="px-6 py-4 border-b border-zinc-200 bg-zinc-50/60">
                        <h3 class="font-semibold text-sm text-zinc-900">Pendapatan per Metode Bayar</h3>
                    </div>
                    @forelse($ringkasan['pendapatan_per_metode'] as $baris)
                        <div class="flex items-center justify-between px-6 py-3.5 border-b border-zinc-100 last:border-b-0 text-sm">
                            <div>
                                <span class="font-medium text-zinc-900 capitalize">{{ $baris['metode'] }}</span>
                                <span class="text-xs text-zinc-400 tabular-nums ml-1">({{ $baris['jumlah'] }}x)</span>
                            </div>
                            <span class="font-semibold text-zinc-900 tabular-nums">{{ $rp($baris['total']) }}</span>
                        </div>
                    @empty
                        <p class="px-6 py-8 text-center text-xs text-zinc-500">Belum ada pembayaran pada periode ini.</p>
                    @endforelse
                </div>

                <div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden shadow-xs">
                    <div class="px-6 py-4 border-b border-zinc-200 bg-zinc-50/60">
                        <h3 class="font-semibold text-sm text-zinc-900">Pengeluaran per Kategori</h3>
                    </div>
                    @forelse($ringkasan['pengeluaran_per_kategori'] as $baris)
                        <div class="flex items-center justify-between px-6 py-3.5 border-b border-zinc-100 last:border-b-0 text-sm">
                            <div>
                                <span class="font-medium text-zinc-900">{{ $baris['kategori'] }}</span>
                                <span class="text-xs text-zinc-400 tabular-nums ml-1">({{ $baris['jumlah'] }}x)</span>
                            </div>
                            <span class="font-semibold text-rose-600 tabular-nums">{{ $rp($baris['total']) }}</span>
                        </div>
                    @empty
                        <p class="px-6 py-8 text-center text-xs text-zinc-500">Belum ada pengeluaran pada periode ini.</p>
                    @endforelse
                </div>
            </div>
        @endif

        {{-- TAB PENDAPATAN (otomatis dari pembayaran) --}}
        @if($tab === 'pendapatan')
            <p class="text-xs text-zinc-500 mb-4">
                Pendapatan dihitung otomatis dari pembayaran pesanan. Untuk mengubahnya, catat atau batalkan pembayaran di halaman pesanan terkait.
            </p>
            <div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden shadow-xs">
                @if($pendapatanList->isEmpty())
                    <div class="py-16 text-center">
                        <p class="font-semibold text-sm text-zinc-900">Belum ada pendapatan pada periode ini</p>
                        <p class="text-xs text-zinc-500 mt-1">Pembayaran yang dicatat pada pesanan akan muncul di sini.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-zinc-200 bg-zinc-50 text-xs font-semibold text-zinc-700 uppercase tracking-wider">
                                    <th class="text-left px-6 py-3.5">Tanggal</th>
                                    <th class="text-left px-6 py-3.5">Pesanan</th>
                                    <th class="text-left px-6 py-3.5 hidden sm:table-cell">Pelanggan</th>
                                    <th class="text-left px-6 py-3.5 hidden md:table-cell">Metode</th>
                                    <th class="text-right px-6 py-3.5">Jumlah</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200">
                                @foreach($pendapatanList as $pembayaran)
                                    <tr class="hover:bg-zinc-50/50 transition-colors">
                                        <td class="px-6 py-3.5 text-zinc-900 whitespace-nowrap tabular-nums">{{ $pembayaran->tanggal_bayar->translatedFormat('d M Y') }}</td>
                                        <td class="px-6 py-3.5">
                                            @if($pembayaran->pesanan)
                                                <a href="{{ route('pesanan.show', $pembayaran->pesanan) }}" class="font-medium text-zinc-900 hover:underline">
                                                    {{ $pembayaran->pesanan->nomor_pesanan }}
                                                </a>
                                            @else
                                                <span class="text-zinc-400">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-3.5 text-zinc-900 hidden sm:table-cell">{{ $pembayaran->pesanan?->pelanggan?->nama ?? '-' }}</td>
                                        <td class="px-6 py-3.5 text-zinc-500 capitalize hidden md:table-cell">{{ $pembayaran->metode }}</td>
                                        <td class="px-6 py-3.5 text-right font-semibold text-zinc-900 whitespace-nowrap tabular-nums">{{ $rp($pembayaran->jumlah) }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-zinc-200 bg-zinc-50/60 font-semibold">
                                    <td colspan="4" class="px-6 py-3.5 text-xs text-zinc-700 uppercase tracking-wider">Total Pendapatan Periode</td>
                                    <td class="px-6 py-3.5 text-right font-bold text-zinc-900 tabular-nums">{{ $rp($ringkasan['pendapatan']) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @if($pendapatanList->hasPages())
                        <div class="px-6 py-4 border-t border-zinc-200">
                            {{ $pendapatanList->links() }}
                        </div>
                    @endif
                @endif
            </div>
        @endif

        {{-- TAB PENGELUARAN (CRUD manual) --}}
        @if($tab === 'pengeluaran')
            <div class="flex justify-end mb-4">
                <button type="button" @click="bukaTambah()"
                        class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium transition-colors shadow-xs cursor-pointer">
                    <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Catat Pengeluaran
                </button>
            </div>

            <div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden shadow-xs">
                @if($pengeluaranList->isEmpty())
                    <div class="py-16 text-center">
                        <p class="font-semibold text-sm text-zinc-900">Belum ada pengeluaran pada periode ini</p>
                        <p class="text-xs text-zinc-500 mt-1">Catat belanja bahan, listrik, sewa, atau biaya usaha lainnya.</p>
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="border-b border-zinc-200 bg-zinc-50 text-xs font-semibold text-zinc-700 uppercase tracking-wider">
                                    <th class="text-left px-6 py-3.5">Tanggal</th>
                                    <th class="text-left px-6 py-3.5">Kategori</th>
                                    <th class="text-left px-6 py-3.5 hidden sm:table-cell">Catatan</th>
                                    <th class="text-right px-6 py-3.5">Nominal</th>
                                    <th class="px-6 py-3.5"></th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-zinc-200">
                                @foreach($pengeluaranList as $pengeluaran)
                                    <tr class="hover:bg-zinc-50/50 transition-colors">
                                        <td class="px-6 py-3.5 text-zinc-900 whitespace-nowrap tabular-nums">{{ $pengeluaran->tanggal->translatedFormat('d M Y') }}</td>
                                        <td class="px-6 py-3.5 text-zinc-900">{{ $pengeluaran->kategori?->nama_kategori ?? 'Tanpa kategori' }}</td>
                                        <td class="px-6 py-3.5 text-zinc-500 hidden sm:table-cell">{{ $pengeluaran->catatan ?? '-' }}</td>
                                        <td class="px-6 py-3.5 text-right font-semibold text-rose-600 whitespace-nowrap tabular-nums">{{ $rp($pengeluaran->nominal) }}</td>
                                        <td class="px-6 py-3.5 text-right whitespace-nowrap">
                                            <button type="button"
                                                    @click="bukaUbah({{ Js::from([
                                                        'id' => $pengeluaran->id,
                                                        'kategori' => $pengeluaran->kategori_pengeluaran_id,
                                                        'nominal' => (float) $pengeluaran->nominal,
                                                        'tanggal' => $pengeluaran->tanggal->toDateString(),
                                                        'catatan' => $pengeluaran->catatan,
                                                    ]) }})"
                                                    class="text-xs font-medium text-zinc-700 hover:text-zinc-900 cursor-pointer">Ubah</button>
                                            <form action="{{ route('keuangan.pengeluaran.destroy', $pengeluaran) }}" method="POST" class="inline"
                                                  onsubmit="return confirm('Hapus catatan pengeluaran ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="ml-3 text-xs font-medium text-rose-600 hover:text-rose-700 cursor-pointer">Hapus</button>
                                            </form>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-zinc-200 bg-zinc-50/60 font-semibold">
                                    <td colspan="3" class="px-6 py-3.5 text-xs text-zinc-700 uppercase tracking-wider">Total Pengeluaran Periode</td>
                                    <td class="px-6 py-3.5 text-right font-bold text-rose-600 tabular-nums">{{ $rp($ringkasan['pengeluaran']) }}</td>
                                    <td></td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                    @if($pengeluaranList->hasPages())
                        <div class="px-6 py-4 border-t border-zinc-200">
                            {{ $pengeluaranList->links() }}
                        </div>
                    @endif
                @endif
            </div>

            {{-- MODAL TAMBAH / UBAH PENGELUARAN --}}
            <div x-show="modalOpen" x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto bg-zinc-900/60 flex items-center justify-center p-4">
                <div @click.away="modalOpen = false"
                     class="bg-white rounded-2xl p-6 sm:p-8 max-w-md w-full border border-zinc-200 shadow-xl">
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-zinc-200">
                        <h3 class="font-semibold text-base text-zinc-900" x-text="edit ? 'Ubah Pengeluaran' : 'Catat Pengeluaran'"></h3>
                        <button type="button" @click="modalOpen = false" class="text-zinc-400 hover:text-zinc-600 cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <form method="POST" class="space-y-4"
                          :action="edit ? '{{ url('keuangan/pengeluaran') }}/' + edit : '{{ route('keuangan.pengeluaran.store') }}'">
                        @csrf
                        <input type="hidden" name="_method" value="PUT" :disabled="!edit">

                        <div>
                            <label class="block text-xs font-medium text-zinc-700 mb-1">Kategori</label>
                            <select name="kategori_pengeluaran_id" x-model="form.kategori"
                                    class="w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900">
                                <option value="">Tanpa kategori</option>
                                @foreach($kategoriList as $kat)
                                    <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
                                @endforeach
                            </select>
                            <p class="text-xs text-zinc-500 mt-1">Kategori baru bisa ditambahkan di menu Pengaturan.</p>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-zinc-700 mb-1">
                                    Nominal (Rp) <span class="text-rose-500">*</span>
                                </label>
                                <input type="number" name="nominal" required min="0.01" step="0.01" x-model="form.nominal"
                                       class="w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 tabular-nums">
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-zinc-700 mb-1">
                                    Tanggal <span class="text-rose-500">*</span>
                                </label>
                                <input type="date" name="tanggal" required max="{{ $hariIni }}" x-model="form.tanggal"
                                       class="w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 tabular-nums">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-zinc-700 mb-1">Catatan (Opsional)</label>
                            <textarea name="catatan" rows="2" maxlength="500" x-model="form.catatan" placeholder="Contoh: Beli benang dan kancing"
                                      class="w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 resize-none"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200">
                            <button type="button" @click="modalOpen = false"
                                    class="px-4 py-2 rounded-lg text-xs font-medium text-zinc-600 hover:text-zinc-900 cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-5 py-2.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium shadow-xs cursor-pointer">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
