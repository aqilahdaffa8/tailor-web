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
              class="bg-white rounded-2xl border border-[#E7DED2] p-4 mb-6 flex flex-wrap items-end gap-3">
            <input type="hidden" name="tab" value="{{ $tab }}">
            <div>
                <label class="block text-[11px] font-semibold text-[#38443D] uppercase tracking-wide mb-1">Dari Tanggal</label>
                <input type="date" name="dari" value="{{ $dari }}"
                       class="px-3 py-2 bg-white border border-[#E7DED2] rounded-xl text-xs focus:border-[#A8754F] focus:outline-none">
            </div>
            <div>
                <label class="block text-[11px] font-semibold text-[#38443D] uppercase tracking-wide mb-1">Sampai Tanggal</label>
                <input type="date" name="sampai" value="{{ $sampai }}"
                       class="px-3 py-2 bg-white border border-[#E7DED2] rounded-xl text-xs focus:border-[#A8754F] focus:outline-none">
            </div>
            @if($tab === 'pengeluaran')
                <div>
                    <label class="block text-[11px] font-semibold text-[#38443D] uppercase tracking-wide mb-1">Kategori</label>
                    <select name="kategori_id"
                            class="px-3 py-2 bg-white border border-[#E7DED2] rounded-xl text-xs focus:border-[#A8754F] focus:outline-none">
                        <option value="">Semua Kategori</option>
                        @foreach($kategoriList as $kat)
                            <option value="{{ $kat->id }}" {{ $kategoriId === $kat->id ? 'selected' : '' }}>{{ $kat->nama_kategori }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <button type="submit"
                    class="px-4 py-2 bg-[#29302D] hover:bg-[#38443D] text-white rounded-xl text-xs font-semibold transition-colors cursor-pointer">
                Terapkan
            </button>
            <a href="{{ route('keuangan.index', ['tab' => $tab]) }}"
               class="px-2.5 py-2 text-xs text-[#9A816B] hover:text-[#29302D] transition-colors">
                Bulan Ini
            </a>
            <p class="ml-auto text-[11px] text-[#9A816B]">Periode: {{ $ringkasan['periode']['label'] }}</p>
        </form>

        {{-- Navigasi Tab --}}
        <div class="flex items-center gap-2 mb-6 border-b border-[#E7DED2] pb-3">
            <a href="{{ $tabUrl('ringkasan') }}"
               class="px-4 py-2 rounded-xl text-xs font-semibold transition-all {{ $tab === 'ringkasan' ? 'bg-[#29302D] text-white' : 'bg-white text-[#9A816B] hover:text-[#29302D] border border-[#E7DED2]' }}">
                Ringkasan
            </a>
            <a href="{{ $tabUrl('pendapatan') }}"
               class="px-4 py-2 rounded-xl text-xs font-semibold transition-all {{ $tab === 'pendapatan' ? 'bg-[#29302D] text-white' : 'bg-white text-[#9A816B] hover:text-[#29302D] border border-[#E7DED2]' }}">
                Pendapatan ({{ $ringkasan['jumlah_pembayaran'] }})
            </a>
            <a href="{{ $tabUrl('pengeluaran') }}"
               class="px-4 py-2 rounded-xl text-xs font-semibold transition-all {{ $tab === 'pengeluaran' ? 'bg-[#29302D] text-white' : 'bg-white text-[#9A816B] hover:text-[#29302D] border border-[#E7DED2]' }}">
                Pengeluaran ({{ $ringkasan['jumlah_pengeluaran'] }})
            </a>
        </div>

        {{-- TAB RINGKASAN --}}
        @if($tab === 'ringkasan')
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                <div class="bg-white rounded-2xl border border-[#E7DED2] p-5">
                    <p class="text-[11px] font-semibold text-[#9A816B] uppercase tracking-wide">Pendapatan</p>
                    <p class="font-heading font-bold text-2xl text-[#38443D] mt-1">{{ $rp($ringkasan['pendapatan']) }}</p>
                    <p class="text-[11px] text-[#9A816B] mt-1">{{ $ringkasan['jumlah_pembayaran'] }} pembayaran masuk</p>
                </div>
                <div class="bg-white rounded-2xl border border-[#E7DED2] p-5">
                    <p class="text-[11px] font-semibold text-[#9A816B] uppercase tracking-wide">Pengeluaran</p>
                    <p class="font-heading font-bold text-2xl text-[#B86B67] mt-1">{{ $rp($ringkasan['pengeluaran']) }}</p>
                    <p class="text-[11px] text-[#9A816B] mt-1">{{ $ringkasan['jumlah_pengeluaran'] }} catatan pengeluaran</p>
                </div>
                <div class="rounded-2xl border p-5 {{ $selisihPositif ? 'bg-[#6F8A78]/10 border-[#6F8A78]/40' : 'bg-[#B86B67]/10 border-[#B86B67]/40' }}">
                    <p class="text-[11px] font-semibold text-[#9A816B] uppercase tracking-wide">Selisih (Pendapatan - Pengeluaran)</p>
                    <p class="font-heading font-bold text-2xl mt-1 {{ $selisihPositif ? 'text-[#38443D]' : 'text-[#B86B67]' }}">
                        {{ $selisihPositif ? '' : '-' }}{{ $rp(abs($ringkasan['selisih'])) }}
                    </p>
                    <p class="text-[11px] text-[#9A816B] mt-1">{{ $selisihPositif ? 'Surplus pada periode ini' : 'Defisit pada periode ini' }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                <div class="bg-white rounded-2xl border border-[#E7DED2] overflow-hidden">
                    <div class="px-6 py-4 border-b border-[#E7DED2] bg-[#F7F4EE]/60">
                        <h3 class="font-heading font-semibold text-sm text-[#29302D]">Pendapatan per Metode Bayar</h3>
                    </div>
                    @forelse($ringkasan['pendapatan_per_metode'] as $baris)
                        <div class="flex items-center justify-between px-6 py-3 border-b border-[#E7DED2] last:border-b-0 text-sm">
                            <div>
                                <span class="font-medium text-[#29302D] capitalize">{{ $baris['metode'] }}</span>
                                <span class="text-[11px] text-[#9A816B] ml-1">({{ $baris['jumlah'] }}x)</span>
                            </div>
                            <span class="font-semibold text-[#38443D]">{{ $rp($baris['total']) }}</span>
                        </div>
                    @empty
                        <p class="px-6 py-8 text-center text-xs text-[#9A816B]">Belum ada pembayaran pada periode ini.</p>
                    @endforelse
                </div>

                <div class="bg-white rounded-2xl border border-[#E7DED2] overflow-hidden">
                    <div class="px-6 py-4 border-b border-[#E7DED2] bg-[#F7F4EE]/60">
                        <h3 class="font-heading font-semibold text-sm text-[#29302D]">Pengeluaran per Kategori</h3>
                    </div>
                    @forelse($ringkasan['pengeluaran_per_kategori'] as $baris)
                        <div class="flex items-center justify-between px-6 py-3 border-b border-[#E7DED2] last:border-b-0 text-sm">
                            <div>
                                <span class="font-medium text-[#29302D]">{{ $baris['kategori'] }}</span>
                                <span class="text-[11px] text-[#9A816B] ml-1">({{ $baris['jumlah'] }}x)</span>
                            </div>
                            <span class="font-semibold text-[#B86B67]">{{ $rp($baris['total']) }}</span>
                        </div>
                    @empty
                        <p class="px-6 py-8 text-center text-xs text-[#9A816B]">Belum ada pengeluaran pada periode ini.</p>
                    @endforelse
                </div>
            </div>
        @endif

        {{-- TAB PENDAPATAN (otomatis dari pembayaran) --}}
        @if($tab === 'pendapatan')
            <p class="text-xs text-[#9A816B] mb-4">
                Pendapatan dihitung otomatis dari pembayaran pesanan. Untuk mengubahnya, catat atau batalkan pembayaran di halaman pesanan terkait.
            </p>
            <div class="bg-white rounded-2xl border border-[#E7DED2] overflow-hidden">
                @if($pendapatanList->isEmpty())
                    <div class="py-16 text-center">
                        <p class="font-heading font-semibold text-sm text-[#29302D]">Belum ada pendapatan pada periode ini</p>
                        <p class="text-xs text-[#9A816B] mt-1">Pembayaran yang dicatat pada pesanan akan muncul di sini.</p>
                    </div>
                @else
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-[#E7DED2] bg-[#F7F4EE]/60">
                                <th class="text-left px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider">Tanggal</th>
                                <th class="text-left px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider">Pesanan</th>
                                <th class="text-left px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider hidden sm:table-cell">Pelanggan</th>
                                <th class="text-left px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider hidden md:table-cell">Metode</th>
                                <th class="text-right px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider">Jumlah</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E7DED2]">
                            @foreach($pendapatanList as $pembayaran)
                                <tr class="hover:bg-[#F7F4EE]/40">
                                    <td class="px-6 py-3.5 text-[#29302D] whitespace-nowrap">{{ $pembayaran->tanggal_bayar->translatedFormat('d M Y') }}</td>
                                    <td class="px-6 py-3.5">
                                        @if($pembayaran->pesanan)
                                            <a href="{{ route('pesanan.show', $pembayaran->pesanan) }}" class="font-medium text-[#A8754F] hover:underline">
                                                {{ $pembayaran->pesanan->nomor_pesanan }}
                                            </a>
                                        @else
                                            <span class="text-[#9A816B]">-</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-3.5 text-[#29302D] hidden sm:table-cell">{{ $pembayaran->pesanan?->pelanggan?->nama ?? '-' }}</td>
                                    <td class="px-6 py-3.5 text-[#9A816B] capitalize hidden md:table-cell">{{ $pembayaran->metode }}</td>
                                    <td class="px-6 py-3.5 text-right font-semibold text-[#38443D] whitespace-nowrap">{{ $rp($pembayaran->jumlah) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-[#E7DED2] bg-[#F7F4EE]/60">
                                <td colspan="4" class="px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider">Total Pendapatan Periode</td>
                                <td class="px-6 py-3.5 text-right font-bold text-[#38443D]">{{ $rp($ringkasan['pendapatan']) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                    @if($pendapatanList->hasPages())
                        <div class="px-6 py-4 border-t border-[#E7DED2]">
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
                        class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#29302D] hover:bg-[#38443D] text-white text-xs font-semibold transition-all cursor-pointer">
                    <svg class="w-4 h-4 text-[#C49A52]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Catat Pengeluaran
                </button>
            </div>

            <div class="bg-white rounded-2xl border border-[#E7DED2] overflow-hidden">
                @if($pengeluaranList->isEmpty())
                    <div class="py-16 text-center">
                        <p class="font-heading font-semibold text-sm text-[#29302D]">Belum ada pengeluaran pada periode ini</p>
                        <p class="text-xs text-[#9A816B] mt-1">Catat belanja bahan, listrik, sewa, atau biaya usaha lainnya.</p>
                    </div>
                @else
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-[#E7DED2] bg-[#F7F4EE]/60">
                                <th class="text-left px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider">Tanggal</th>
                                <th class="text-left px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider">Kategori</th>
                                <th class="text-left px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider hidden sm:table-cell">Catatan</th>
                                <th class="text-right px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider">Nominal</th>
                                <th class="px-6 py-3.5"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-[#E7DED2]">
                            @foreach($pengeluaranList as $pengeluaran)
                                <tr class="hover:bg-[#F7F4EE]/40">
                                    <td class="px-6 py-3.5 text-[#29302D] whitespace-nowrap">{{ $pengeluaran->tanggal->translatedFormat('d M Y') }}</td>
                                    <td class="px-6 py-3.5 text-[#29302D]">{{ $pengeluaran->kategori?->nama_kategori ?? 'Tanpa kategori' }}</td>
                                    <td class="px-6 py-3.5 text-[#9A816B] hidden sm:table-cell">{{ $pengeluaran->catatan ?? '-' }}</td>
                                    <td class="px-6 py-3.5 text-right font-semibold text-[#B86B67] whitespace-nowrap">{{ $rp($pengeluaran->nominal) }}</td>
                                    <td class="px-6 py-3.5 text-right whitespace-nowrap">
                                        <button type="button"
                                                @click="bukaUbah({{ Js::from([
                                                    'id' => $pengeluaran->id,
                                                    'kategori' => $pengeluaran->kategori_pengeluaran_id,
                                                    'nominal' => (float) $pengeluaran->nominal,
                                                    'tanggal' => $pengeluaran->tanggal->toDateString(),
                                                    'catatan' => $pengeluaran->catatan,
                                                ]) }})"
                                                class="text-xs font-semibold text-[#A8754F] hover:underline cursor-pointer">Ubah</button>
                                        <form action="{{ route('keuangan.pengeluaran.destroy', $pengeluaran) }}" method="POST" class="inline"
                                              onsubmit="return confirm('Hapus catatan pengeluaran ini?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="ml-3 text-xs font-semibold text-[#B86B67] hover:underline cursor-pointer">Hapus</button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="border-t border-[#E7DED2] bg-[#F7F4EE]/60">
                                <td colspan="3" class="px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider">Total Pengeluaran Periode</td>
                                <td class="px-6 py-3.5 text-right font-bold text-[#B86B67]">{{ $rp($ringkasan['pengeluaran']) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    </table>
                    @if($pengeluaranList->hasPages())
                        <div class="px-6 py-4 border-t border-[#E7DED2]">
                            {{ $pengeluaranList->links() }}
                        </div>
                    @endif
                @endif
            </div>

            {{-- MODAL TAMBAH / UBAH PENGELUARAN --}}
            <div x-show="modalOpen" x-cloak
                 class="fixed inset-0 z-50 overflow-y-auto bg-black/40 flex items-center justify-center p-4">
                <div @click.away="modalOpen = false"
                     class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full border border-[#E7DED2]">
                    <div class="flex items-center justify-between pb-4 mb-4 border-b border-[#E7DED2]">
                        <h3 class="font-heading font-bold text-base text-[#29302D]" x-text="edit ? 'Ubah Pengeluaran' : 'Catat Pengeluaran'"></h3>
                        <button type="button" @click="modalOpen = false" class="text-[#9A816B] hover:text-[#29302D] cursor-pointer">
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
                            <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">Kategori</label>
                            <select name="kategori_pengeluaran_id" x-model="form.kategori"
                                    class="w-full px-3 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                                <option value="">Tanpa kategori</option>
                                @foreach($kategoriList as $kat)
                                    <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
                                @endforeach
                            </select>
                            <p class="text-[11px] text-[#9A816B] mt-1">Kategori baru bisa ditambahkan di menu Pengaturan.</p>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
                                    Nominal (Rp) <span class="text-[#B86B67]">*</span>
                                </label>
                                <input type="number" name="nominal" required min="0.01" step="0.01" x-model="form.nominal"
                                       class="w-full px-3 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
                                    Tanggal <span class="text-[#B86B67]">*</span>
                                </label>
                                <input type="date" name="tanggal" required max="{{ $hariIni }}" x-model="form.tanggal"
                                       class="w-full px-3 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">Catatan (Opsional)</label>
                            <textarea name="catatan" rows="2" maxlength="500" x-model="form.catatan" placeholder="Contoh: Beli benang dan kancing"
                                      class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none resize-none"></textarea>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#E7DED2]">
                            <button type="button" @click="modalOpen = false"
                                    class="px-4 py-2 rounded-xl text-xs font-semibold text-[#9A816B] hover:text-[#29302D] cursor-pointer">
                                Batal
                            </button>
                            <button type="submit"
                                    class="px-5 py-2.5 rounded-xl bg-[#29302D] hover:bg-[#38443D] text-white text-xs font-semibold cursor-pointer">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        @endif
    </div>
</x-app-layout>
