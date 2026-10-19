<x-app-layout title="Pelanggan">
    <x-slot name="header">
        Daftar Pelanggan
    </x-slot>

    {{-- Toolbar: Search & Tambah Pelanggan --}}
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-6">
        <form method="GET" action="{{ route('pelanggan.index') }}" class="flex items-center gap-2 flex-1 max-w-md">
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
                <a href="{{ route('pelanggan.index') }}"
                   class="px-2.5 py-2 text-xs font-medium text-zinc-500 hover:text-zinc-900 transition-colors shrink-0">
                    Reset
                </a>
            @endif
        </form>

        <a href="{{ route('pelanggan.create') }}"
           class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-medium transition-colors shadow-2xs shrink-0">
            <svg class="w-4 h-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Tambah Pelanggan</span>
        </a>
    </div>

    {{-- Tabel Pelanggan --}}
    <div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden shadow-2xs">
        @if($pelanggan->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-12 h-12 rounded-xl bg-zinc-100 border border-zinc-200 flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <p class="font-semibold text-sm text-zinc-900">Belum ada pelanggan</p>
                <p class="text-xs text-zinc-500 mt-1">
                    @if($search)
                        Tidak ada hasil untuk pencarian "{{ $search }}"
                    @else
                        Mulai tambah pelanggan pertama Anda
                    @endif
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm table-mobile-10">
                    <thead>
                        <tr class="border-b border-zinc-200 bg-zinc-50/80">
                            <th class="text-left px-6 py-3.5 text-xs font-medium text-zinc-500">Nama Pelanggan</th>
                            <th class="text-left px-6 py-3.5 text-xs font-medium text-zinc-500 hidden sm:table-cell">No. WhatsApp / HP</th>
                            <th class="text-center px-6 py-3.5 text-xs font-medium text-zinc-500 hidden md:table-cell">Total Pesanan</th>
                            <th class="text-left px-6 py-3.5 text-xs font-medium text-zinc-500 hidden lg:table-cell">Bergabung</th>
                            <th class="text-right px-6 py-3.5 text-xs font-medium text-zinc-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @foreach($pelanggan as $item)
                            <tr class="hover:bg-zinc-50/60 transition-colors">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-9 h-9 rounded-full bg-zinc-100 border border-zinc-200 text-zinc-700 flex items-center justify-center font-semibold text-xs shrink-0">
                                            {{ strtoupper(substr($item->nama, 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <a href="{{ route('pelanggan.show', $item) }}" class="font-semibold text-zinc-900 hover:text-zinc-600 transition-colors truncate block">
                                                {{ $item->nama }}
                                            </a>
                                            <p class="text-xs text-zinc-500 sm:hidden mt-0.5">{{ $item->no_hp }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-zinc-600 hidden sm:table-cell">{{ $item->no_hp }}</td>
                                <td class="px-6 py-4 text-center hidden md:table-cell">
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 text-zinc-700 border border-zinc-200 tabular-nums">
                                        {{ $item->pesanan_count }} pesanan
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-xs text-zinc-500 hidden lg:table-cell">
                                    {{ $item->created_at->format('d M Y') }}
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('pelanggan.show', $item) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-200 text-xs font-medium text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900 transition-colors">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($pelanggan->hasPages())
                <div class="px-6 py-4 border-t border-zinc-100">
                    {{ $pelanggan->links() }}
                </div>
            @endif
        @endif
    </div>
</x-app-layout>
