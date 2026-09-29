<x-app-layout title="Pengukuran Pelanggan">
    <x-slot name="header">
        Data Pengukuran Tubuh
    </x-slot>

    {{-- Search & Tambah --}}
    <div class="flex items-center gap-2 mb-6">
        <form method="GET" action="{{ route('pengukuran.index') }}" class="flex items-center gap-2 flex-1 max-w-sm">
            <input type="text" name="search" value="{{ $search }}"
                   placeholder="Cari nama atau no. HP pelanggan..."
                   class="w-full px-4 py-2.5 bg-white border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
            <button type="submit"
                    class="px-4 py-2.5 bg-[#F7F4EE] border border-[#E7DED2] rounded-xl text-xs font-semibold text-[#38443D] hover:bg-[#E7DED2] transition-colors cursor-pointer shrink-0">
                Cari
            </button>
            @if($search)
                <a href="{{ route('pengukuran.index') }}"
                   class="px-3 py-2.5 text-xs text-[#9A816B] hover:text-[#29302D] transition-colors shrink-0">
                    Reset
                </a>
            @endif
        </form>

        <a href="{{ route('pengukuran.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#29302D] hover:bg-[#38443D] text-white text-xs font-semibold transition-all shrink-0 ml-auto">
            <svg class="w-4 h-4 text-[#C49A52]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Ukur Pelanggan Baru
        </a>
    </div>

    {{-- Tabel Pengukuran --}}
    <div class="bg-white rounded-2xl border border-[#E7DED2] overflow-hidden">
        @if($pengukuranList->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-16 h-16 rounded-full bg-[#F7F4EE] border border-[#E7DED2] flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-[#9A816B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="font-heading font-semibold text-[#29302D]">Belum ada data pengukuran</p>
                <p class="text-xs text-[#9A816B] mt-1">
                    @if($search)
                        Tidak ada hasil untuk "{{ $search }}"
                    @else
                        Mulai mencatat ukuran tubuh pelanggan untuk pesanan jahit.
                    @endif
                </p>
            </div>
        @else
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-[#E7DED2] bg-[#F7F4EE]/60">
                        <th class="text-left px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider">Pelanggan</th>
                        <th class="text-left px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider hidden sm:table-cell">Tanggal Ukur</th>
                        <th class="text-left px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider hidden md:table-cell">Ringkasan Dimensi</th>
                        <th class="text-left px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider hidden lg:table-cell">Catatan</th>
                        <th class="px-6 py-3.5"></th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-[#E7DED2]">
                    @foreach($pengukuranList as $item)
                        <tr class="hover:bg-[#F7F4EE]/50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-full bg-[#A8754F] text-white flex items-center justify-center font-bold text-sm shrink-0">
                                        {{ strtoupper(substr($item->pelanggan->nama ?? 'P', 0, 1)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('pelanggan.show', $item->pelanggan_id) }}" class="font-medium text-[#29302D] hover:text-[#A8754F] transition-colors">
                                            {{ $item->pelanggan->nama ?? 'Pelanggan' }}
                                        </a>
                                        <p class="text-xs text-[#9A816B]">{{ $item->pelanggan->no_hp ?? '-' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-[#38443D] text-xs hidden sm:table-cell">
                                {{ $item->tanggal_ukur->format('d M Y') }}
                            </td>
                            <td class="px-6 py-4 hidden md:table-cell">
                                <div class="flex flex-wrap gap-1.5 text-[11px]">
                                    @if(isset($item->data_ukuran['lingkar_dada']))
                                        <span class="px-2 py-0.5 rounded-md bg-[#F7F4EE] border border-[#E7DED2] text-[#38443D]">
                                            Dada: {{ $item->data_ukuran['lingkar_dada'] }} cm
                                        </span>
                                    @endif
                                    @if(isset($item->data_ukuran['lingkar_pinggang']))
                                        <span class="px-2 py-0.5 rounded-md bg-[#F7F4EE] border border-[#E7DED2] text-[#38443D]">
                                            Pinggang: {{ $item->data_ukuran['lingkar_pinggang'] }} cm
                                        </span>
                                    @endif
                                    @if(isset($item->data_ukuran['panjang_baju']))
                                        <span class="px-2 py-0.5 rounded-md bg-[#F7F4EE] border border-[#E7DED2] text-[#38443D]">
                                            Pjg Baju: {{ $item->data_ukuran['panjang_baju'] }} cm
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 text-xs text-[#9A816B] max-w-xs truncate hidden lg:table-cell">
                                {{ $item->catatan ?? '-' }}
                            </td>
                            <td class="px-6 py-4 text-right">
                                <a href="{{ route('pengukuran.show', $item) }}"
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-[#E7DED2] text-xs font-medium text-[#38443D] hover:bg-[#F7F4EE] transition-colors">
                                    Detail
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            @if($pengukuranList->hasPages())
                <div class="px-6 py-4 border-t border-[#E7DED2]">
                    {{ $pengukuranList->links() }}
                </div>
            @endif
        @endif
    </div>

    @if($pengukuranList->total() > 0)
        <p class="mt-3 text-xs text-[#9A816B] text-right">
            Menampilkan {{ $pengukuranList->firstItem() }}–{{ $pengukuranList->lastItem() }} dari {{ $pengukuranList->total() }} catatan ukuran
        </p>
    @endif
</x-app-layout>
