<x-app-layout title="Riwayat Stok - {{ $bahan->nama_bahan }}">
    <x-slot name="header">
        Riwayat Mutasi Stok
    </x-slot>

    {{-- Tombol Kembali --}}
    <div class="mb-5">
        <a href="{{ route('inventory.index', ['tab' => 'bahan']) }}"
           class="inline-flex items-center gap-2 text-sm text-zinc-500 hover:text-zinc-900 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Inventory Bahan
        </a>
    </div>

    {{-- Header Info Bahan --}}
    <div class="bg-white rounded-2xl border border-zinc-200 p-6 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-xl bg-zinc-900 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-zinc-100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4"/>
                </svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-zinc-900">{{ $bahan->nama_bahan }}</h2>
                <p class="text-xs text-zinc-500 mt-0.5">
                    {{ $bahan->kategoriBahan?->nama_kategori ?? '-' }}
                    &middot; Stok saat ini:
                    <span class="font-semibold tabular-nums {{ $bahan->isMenipis() ? 'text-rose-600' : 'text-zinc-900' }}">
                        {{ number_format($bahan->stok, 2, ',', '.') }} {{ $bahan->satuan }}
                    </span>
                    @if($bahan->isMenipis())
                        <span class="ml-1 text-xs px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200/60 font-medium">Menipis</span>
                    @endif
                </p>
            </div>
        </div>

        <div class="shrink-0 sm:text-right">
            <p class="text-xs text-zinc-500">Batas minimum stok</p>
            <p class="font-bold text-base text-zinc-900 tabular-nums">{{ number_format($bahan->stok_minimum, 2, ',', '.') }} {{ $bahan->satuan }}</p>
        </div>
    </div>

    {{-- Tabel Riwayat Mutasi Stok --}}
    <div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden shadow-xs">
        <div class="p-5 border-b border-zinc-200 flex items-center justify-between">
            <div>
                <h3 class="font-semibold text-sm text-zinc-900">Riwayat Mutasi Stok</h3>
                <p class="text-xs text-zinc-500 mt-0.5">Total {{ $riwayat->count() }} catatan mutasi</p>
            </div>
        </div>

        @if($riwayat->isEmpty())
            <div class="p-12 text-center">
                <div class="w-12 h-12 mx-auto rounded-xl bg-zinc-100 flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <p class="text-sm font-semibold text-zinc-900">Belum ada riwayat mutasi</p>
                <p class="text-xs text-zinc-500 mt-1">Mutasi stok akan tercatat di sini saat terjadi penambahan atau pemakaian bahan.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-xs">
                    <thead>
                        <tr class="bg-zinc-50 border-b border-zinc-200 text-xs font-semibold text-zinc-700 uppercase tracking-wider">
                            <th class="text-left px-5 py-3">Tanggal & Waktu</th>
                            <th class="text-center px-4 py-3">Jenis</th>
                            <th class="text-right px-4 py-3">Jumlah</th>
                            <th class="text-right px-4 py-3">Stok Sebelum</th>
                            <th class="text-right px-4 py-3">Stok Sesudah</th>
                            <th class="text-left px-5 py-3">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200">
                        @foreach($riwayat as $mutasi)
                            <tr class="hover:bg-zinc-50/50 transition-colors">
                                <td class="px-5 py-3.5 whitespace-nowrap">
                                    <p class="font-medium text-zinc-900 tabular-nums">{{ $mutasi->created_at->format('d M Y') }}</p>
                                    <p class="text-xs text-zinc-400 tabular-nums mt-0.5">{{ $mutasi->created_at->format('H:i') }} WIB</p>
                                </td>
                                <td class="px-4 py-3.5 text-center">
                                    @if($mutasi->jenis === 'masuk')
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                                            </svg>
                                            Masuk
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-medium bg-rose-50 text-rose-700 border border-rose-200/60">
                                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                                            </svg>
                                            Keluar
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3.5 text-right whitespace-nowrap">
                                    <span class="font-semibold tabular-nums {{ $mutasi->jenis === 'masuk' ? 'text-emerald-700' : 'text-rose-600' }}">
                                        {{ $mutasi->jenis === 'masuk' ? '+' : '-' }}{{ number_format($mutasi->jumlah, 2, ',', '.') }}
                                    </span>
                                    <span class="text-xs text-zinc-500 ml-0.5">{{ $bahan->satuan }}</span>
                                </td>
                                <td class="px-4 py-3.5 text-right text-zinc-600 whitespace-nowrap tabular-nums">
                                    {{ number_format($mutasi->stok_sebelum, 2, ',', '.') }}
                                    <span class="text-xs text-zinc-400">{{ $bahan->satuan }}</span>
                                </td>
                                <td class="px-4 py-3.5 text-right font-medium text-zinc-900 whitespace-nowrap tabular-nums">
                                    {{ number_format($mutasi->stok_sesudah, 2, ',', '.') }}
                                    <span class="text-xs text-zinc-500 font-normal">{{ $bahan->satuan }}</span>
                                </td>
                                <td class="px-5 py-3.5 text-zinc-600 max-w-xs">
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
