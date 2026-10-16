<x-app-layout title="Pengukuran Pelanggan">
    <x-slot name="header">
        Data Pengukuran Tubuh
    </x-slot>

    {{-- Toolbar: Search & Ukur Baru --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <form method="GET" action="{{ route('pengukuran.index') }}" class="flex items-center gap-2 flex-1 max-w-md">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Cari nama atau no. HP pelanggan..."
                       class="w-full pl-9 pr-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 transition-colors">
            </div>

            <button type="submit"
                    class="px-3.5 py-2 bg-zinc-100 hover:bg-zinc-200 border border-zinc-200 rounded-lg text-xs font-medium text-zinc-700 transition-colors cursor-pointer shrink-0">
                Cari
            </button>
            @if($search)
                <a href="{{ route('pengukuran.index') }}"
                   class="px-2.5 py-2 text-xs font-medium text-zinc-500 hover:text-zinc-900 transition-colors shrink-0">
                    Reset
                </a>
            @endif
        </form>

        <a href="{{ route('pengukuran.create') }}"
           class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-medium transition-colors shadow-2xs shrink-0">
            <svg class="w-4 h-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Ukur Pelanggan Baru</span>
        </a>
    </div>

    {{-- Tabel Pengukuran --}}
    <div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden shadow-2xs">
        @if($pengukuranList->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-12 h-12 rounded-xl bg-zinc-100 border border-zinc-200 flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="font-semibold text-sm text-zinc-900">Belum ada data pengukuran</p>
                <p class="text-xs text-zinc-500 mt-1">
                    @if($search)
                        Tidak ada hasil untuk pencarian "{{ $search }}"
                    @else
                        Mulai mencatat ukuran tubuh pelanggan untuk pesanan jahit.
                    @endif
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 bg-zinc-50/80">
                            <th class="text-left px-6 py-3.5 text-xs font-medium text-zinc-500">Pelanggan</th>
                            <th class="text-left px-6 py-3.5 text-xs font-medium text-zinc-500 hidden sm:table-cell">Tanggal Ukur</th>
                            <th class="text-left px-6 py-3.5 text-xs font-medium text-zinc-500 hidden md:table-cell">Ringkasan Dimensi</th>
                            <th class="text-left px-6 py-3.5 text-xs font-medium text-zinc-500 hidden lg:table-cell">Catatan</th>
                            <th class="text-right px-6 py-3.5 text-xs font-medium text-zinc-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @foreach($pengukuranList as $item)
                            <tr class="hover:bg-zinc-50/60 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-zinc-100 border border-zinc-200 text-zinc-700 flex items-center justify-center font-semibold text-xs shrink-0">
                                            {{ strtoupper(substr($item->pelanggan->nama ?? 'P', 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ route('pelanggan.show', $item->pelanggan_id) }}" class="font-semibold text-zinc-900 hover:text-zinc-600 transition-colors truncate block">
                                                {{ $item->pelanggan->nama ?? 'Pelanggan' }}
                                            </a>
                                            <p class="text-xs text-zinc-500 mt-0.5">{{ $item->pelanggan->no_hp ?? '-' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-zinc-600 text-sm hidden sm:table-cell">
                                    {{ $item->tanggal_ukur->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 hidden md:table-cell">
                                    <div class="flex flex-wrap gap-1.5 text-xs">
                                        @if(isset($item->data_ukuran['lingkar_dada']))
                                            <span class="px-2 py-0.5 rounded-md bg-zinc-100 border border-zinc-200 text-zinc-700 tabular-nums">
                                                Dada: {{ $item->data_ukuran['lingkar_dada'] }} cm
                                            </span>
                                        @endif
                                        @if(isset($item->data_ukuran['lingkar_pinggang']))
                                            <span class="px-2 py-0.5 rounded-md bg-zinc-100 border border-zinc-200 text-zinc-700 tabular-nums">
                                                Pinggang: {{ $item->data_ukuran['lingkar_pinggang'] }} cm
                                            </span>
                                        @endif
                                        @if(isset($item->data_ukuran['panjang_baju']))
                                            <span class="px-2 py-0.5 rounded-md bg-zinc-100 border border-zinc-200 text-zinc-700 tabular-nums">
                                                Pjg Baju: {{ $item->data_ukuran['panjang_baju'] }} cm
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-xs text-zinc-500 max-w-xs truncate hidden lg:table-cell">
                                    {{ $item->catatan ?: '-' }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('pengukuran.show', $item) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-200 text-xs font-medium text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900 transition-colors">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($pengukuranList->hasPages())
                <div class="px-6 py-4 border-t border-zinc-100">
                    {{ $pengukuranList->links() }}
                </div>
            @endif
        @endif
    </div>
</x-app-layout>
