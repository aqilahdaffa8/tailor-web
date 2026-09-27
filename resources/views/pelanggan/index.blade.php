<x-app-layout title="Pelanggan">
    <x-slot name="header">
        Daftar Pelanggan
    </x-slot>

    {{-- Search & Tambah --}}
    <div class="flex items-center gap-2 mb-6">
        <form method="GET" action="{{ route('pelanggan.index') }}" class="flex items-center gap-2 flex-1 max-w-sm">
            <input type="text" name="search" value="{{ $search }}"
                   placeholder="Cari nama atau no. HP..."
                   class="w-full px-4 py-2.5 bg-white border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
            <button type="submit"
                    class="px-4 py-2.5 bg-[#F7F4EE] border border-[#E7DED2] rounded-xl text-xs font-semibold text-[#38443D] hover:bg-[#E7DED2] transition-colors cursor-pointer shrink-0">
                Cari
            </button>
            @if($search)
                <a href="{{ route('pelanggan.index') }}"
                   class="px-3 py-2.5 text-xs text-[#9A816B] hover:text-[#29302D] transition-colors shrink-0">
                    Reset
                </a>
            @endif
        </form>

        <a href="{{ route('pelanggan.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#29302D] hover:bg-[#38443D] text-white text-xs font-semibold transition-all shrink-0 ml-auto">
            <svg class="w-4 h-4 text-[#C49A52]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Pelanggan
        </a>
    </div>

    {{-- Table --}}
    <div class="bg-white rounded-2xl border border-[#E7DED2] overflow-hidden">
        @if($pelanggan->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-16 h-16 rounded-2xl bg-[#F7F4EE] border border-[#E7DED2] flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-[#9A816B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <p class="font-heading font-semibold text-[#29302D]">Belum ada pelanggan</p>
                <p class="text-xs text-[#9A816B] mt-1">
                    @if($search)
                        Tidak ada hasil untuk "{{ $search }}"
                    @else
                        Mulai tambah pelanggan pertama Anda
                    @endif
                </p>
            </div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-[#E7DED2] bg-[#F7F4EE]/60">
                        <th class="text-left px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider">Nama</th>
                        <th class="text-left px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider hidden sm:table-cell">No. HP</th>
                        <th class="text-center px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider hidden md:table-cell">Total Pesanan</th>
                        <th class="text-left px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider hidden lg:table-cell">Bergabung</th>
                        <th class="px-6 py-3.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E7DED2]">
                    @foreach($pelanggan as $item)
                        <tr class="hover:bg-[#F7F4EE]/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-[#A8754F] text-white flex items-center justify-center font-bold text-sm shrink-0">
                                        {{ strtoupper(substr($item->nama, 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-medium text-[#29302D]">{{ $item->nama }}</p>
                                        <p class="text-xs text-[#9A816B] sm:hidden">{{ $item->no_hp }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-[#38443D] hidden sm:table-cell">{{ $item->no_hp }}</td>
                            <td class="px-6 py-4 text-center hidden md:table-cell">
                                <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-[#F7F4EE] border border-[#E7DED2] text-[#38443D]">
                                    {{ $item->pesanan_count }} pesanan
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-[#9A816B] hidden lg:table-cell">
                                {{ $item->created_at->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('pelanggan.show', $item) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-[#E7DED2] text-xs font-medium text-[#38443D] hover:bg-[#F7F4EE] transition-colors">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($pelanggan->hasPages())
                <div class="px-6 py-4 border-t border-[#E7DED2]">
                    {{ $pelanggan->links() }}
                </div>
            @endif
        @endif
    </div>

    @if($pelanggan->total() > 0)
        <p class="mt-3 text-xs text-[#9A816B] text-right">
            Menampilkan {{ $pelanggan->firstItem() }}–{{ $pelanggan->lastItem() }} dari {{ $pelanggan->total() }} pelanggan
        </p>
    @endif
</x-app-layout>
