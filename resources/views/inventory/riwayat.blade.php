<x-app-layout title="Riwayat Stok - {{ $bahan->nama_bahan }}">
    <x-slot name="header">
        Riwayat Mutasi Stok
    </x-slot>

    {{-- Tombol Kembali --}}
    <div class="mb-5">
        <a href="{{ route('inventory.index', ['tab' => 'bahan']) }}"
           class="inline-flex items-center gap-2 text-sm text-[#9A816B] hover:text-[#29302D] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Inventory Bahan
        </a>
    </div>

    {{-- Header Info Bahan --}}
    <div class="bg-white rounded-2xl border border-[#E7DED2] p-6 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-[#29302D] flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-[#C49A52]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
            </div>
            <div>
                <h2 class="font-heading font-bold text-xl text-[#29302D]">{{ $bahan->nama_bahan }}</h2>
                <p class="text-xs text-[#9A816B] mt-0.5">
                    {{ $bahan->kategoriBahan?->nama_kategori ?? '-' }}
                    &middot; Stok saat ini:
                    <span class="font-semibold {{ $bahan->isMenipis() ? 'text-[#B86B67]' : 'text-[#29302D]' }}">
                        {{ number_format($bahan->stok, 2, ',', '.') }} {{ $bahan->satuan }}
                    </span>
                    @if($bahan->isMenipis())
                        <span class="ml-1 text-[10px] px-2 py-0.5 rounded-full bg-[#B86B67]/10 text-[#B86B67] border border-[#B86B67]/20 font-semibold">Menipis</span>
                    @endif
                </p>
            </div>
        </div>

        <div class="shrink-0">
            <p class="text-[11px] text-[#9A816B]">Minimum stok</p>
            <p class="font-heading font-bold text-base text-[#29302D]">{{ number_format($bahan->stok_minimum, 2, ',', '.') }} {{ $bahan->satuan }}</p>
        </div>
    </div>

    {{-- Tabel Riwayat Mutasi Stok --}}
    <div class="bg-white rounded-2xl border border-[#E7DED2] overflow-hidden">
        <div class="p-5 border-b border-[#E7DED2] flex items-center justify-between">
            <div>
                <h3 class="font-heading font-semibold text-sm text-[#29302D]">Riwayat Mutasi Stok</h3>
                <p class="text-[11px] text-[#9A816B] mt-0.5">Total {{ $riwayat->count() }} catatan mutasi</p>
            </div>
        </div>

        @if($riwayat->isEmpty())
            <div class="p-12 text-center">
                <div class="w-12 h-12 mx-auto rounded-2xl bg-[#F7F4EE] flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-[#C2B09A]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-[#29302D]">Belum ada riwayat mutasi</p>
                <p class="text-xs text-[#9A816B] mt-1">Mutasi stok akan tercatat di sini saat terjadi penambahan atau pemakaian bahan.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="bg-[#F7F4EE] border-b border-[#E7DED2]">
                            <th class="text-left px-5 py-3 font-semibold text-[#9A816B] uppercase tracking-wide text-[10px]">Tanggal & Waktu</th>
                            <th class="text-center px-4 py-3 font-semibold text-[#9A816B] uppercase tracking-wide text-[10px]">Jenis</th>
                            <th class="text-right px-4 py-3 font-semibold text-[#9A816B] uppercase tracking-wide text-[10px]">Jumlah</th>
                            <th class="text-right px-4 py-3 font-semibold text-[#9A816B] uppercase tracking-wide text-[10px]">Stok Sebelum</th>
                            <th class="text-right px-4 py-3 font-semibold text-[#9A816B] uppercase tracking-wide text-[10px]">Stok Sesudah</th>
                            <th class="text-left px-5 py-3 font-semibold text-[#9A816B] uppercase tracking-wide text-[10px]">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#F0EAE1]">
                        @foreach($riwayat as $mutasi)
                            <tr class="hover:bg-[#F7F4EE]/50 transition-colors">
                                <td class="px-5 py-3.5">
                                    <p class="font-medium text-[#29302D]">{{ $mutasi->created_at->format('d M Y') }}</p>
                                    <p class="text-[10px] text-[#9A816B] mt-0.5">{{ $mutasi->created_at->format('H:i') }} WIB</p>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($mutasi->jenis === 'masuk')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                            </svg>
                                            Masuk
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-[10px] font-semibold bg-[#B86B67]/10 text-[#B86B67] border border-[#B86B67]/20">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                                            </svg>
                                            Keluar
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <span class="font-heading font-bold {{ $mutasi->jenis === 'masuk' ? 'text-emerald-700' : 'text-[#B86B67]' }}">
                                        {{ $mutasi->jenis === 'masuk' ? '+' : '-' }}{{ number_format($mutasi->jumlah, 2, ',', '.') }}
                                    </span>
                                    <span class="text-[10px] text-[#9A816B] ml-0.5">{{ $bahan->satuan }}</span>
                                </td>
                                <td class="px-4 py-3.5 text-right text-[#38443D]">
                                    {{ number_format($mutasi->stok_sebelum, 2, ',', '.') }}
                                    <span class="text-[10px] text-[#9A816B]">{{ $bahan->satuan }}</span>
                                </td>
                                <td class="px-4 py-3.5 text-right font-semibold text-[#29302D]">
                                    {{ number_format($mutasi->stok_sesudah, 2, ',', '.') }}
                                    <span class="text-[10px] text-[#9A816B] font-normal">{{ $bahan->satuan }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-[#38443D] max-w-xs">
                                    {{ $mutasi->keterangan ?? '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</x-app-layout>
