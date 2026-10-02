<x-app-layout title="Pesanan Jahit">
    <x-slot name="header">
        Pesanan Jahit
    </x-slot>

    @php
        $adaFilter = collect($filters)->filter()->isNotEmpty();
        $hariIni = today();
    @endphp

    {{-- Filter & Tambah --}}
    <div class="flex flex-col xl:flex-row xl:items-center gap-3 mb-6">
        <form method="GET" action="{{ route('pesanan.index') }}" class="flex flex-wrap items-center gap-2 flex-1">
            <input type="text" name="search" value="{{ $filters['search'] }}"
                   placeholder="Cari nomor pesanan atau pelanggan..."
                   class="w-full sm:w-64 px-4 py-2.5 bg-white border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">

            <select name="status"
                    class="px-3 py-2.5 bg-white border border-[#E7DED2] rounded-xl text-xs font-medium text-[#38443D] focus:border-[#A8754F] focus:outline-none">
                <option value="">Semua Status</option>
                @foreach(['aktif' => 'Aktif', 'selesai' => 'Selesai', 'diambil' => 'Diambil', 'dibatalkan' => 'Dibatalkan'] as $nilai => $label)
                    <option value="{{ $nilai }}" @selected($filters['status'] === $nilai)>{{ $label }}</option>
                @endforeach
            </select>

            <select name="deadline"
                    class="px-3 py-2.5 bg-white border border-[#E7DED2] rounded-xl text-xs font-medium text-[#38443D] focus:border-[#A8754F] focus:outline-none">
                <option value="">Semua Deadline</option>
                <option value="terlambat" @selected($filters['deadline'] === 'terlambat')>Terlambat</option>
                <option value="mendekati" @selected($filters['deadline'] === 'mendekati')>Mendekati (H-3)</option>
                <option value="hari_ini" @selected($filters['deadline'] === 'hari_ini')>Deadline Hari Ini</option>
            </select>

            <select name="pelanggan_id"
                    class="px-3 py-2.5 bg-white border border-[#E7DED2] rounded-xl text-xs font-medium text-[#38443D] focus:border-[#A8754F] focus:outline-none max-w-[12rem]">
                <option value="">Semua Pelanggan</option>
                @foreach($daftarPelanggan as $pelanggan)
                    <option value="{{ $pelanggan->id }}" @selected((int) $filters['pelanggan_id'] === $pelanggan->id)>{{ $pelanggan->nama }}</option>
                @endforeach
            </select>

            <button type="submit"
                    class="px-4 py-2.5 bg-[#F7F4EE] border border-[#E7DED2] rounded-xl text-xs font-semibold text-[#38443D] hover:bg-[#E7DED2] transition-colors cursor-pointer shrink-0">
                Terapkan
            </button>
            @if($adaFilter)
                <a href="{{ route('pesanan.index') }}" class="px-3 py-2.5 text-xs text-[#9A816B] hover:text-[#29302D] transition-colors shrink-0">
                    Reset
                </a>
            @endif
        </form>

        <a href="{{ route('pesanan.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#29302D] hover:bg-[#38443D] text-white text-xs font-semibold transition-all shrink-0 xl:ml-auto">
            <svg class="w-4 h-4 text-[#C49A52]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Buat Pesanan Baru
        </a>
    </div>

    {{-- Tabel Pesanan --}}
    <div class="bg-white rounded-2xl border border-[#E7DED2] overflow-hidden">
        @if($pesananList->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-16 h-16 rounded-full bg-[#F7F4EE] border border-[#E7DED2] flex items-center justify-center mb-4">
                    <svg class="w-8 h-8 text-[#9A816B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <p class="font-heading font-semibold text-[#29302D]">Belum ada pesanan</p>
                <p class="text-xs text-[#9A816B] mt-1">
                    @if($adaFilter)
                        Tidak ada pesanan yang cocok dengan filter yang dipilih.
                    @else
                        Buat pesanan jahit pertama untuk pelanggan Anda.
                    @endif
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-[#E7DED2] bg-[#F7F4EE]/60">
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider">Pesanan</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider">Pelanggan</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider">Deadline</th>
                            <th class="text-right px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider">Total</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider">Pembayaran</th>
                            <th class="text-left px-6 py-3.5 text-xs font-semibold text-[#38443D] uppercase tracking-wider">Status</th>
                            <th class="px-6 py-3.5"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#E7DED2]">
                        @foreach($pesananList as $pesanan)
                            @php
                                $sisaHari = (int) $hariIni->diffInDays($pesanan->deadline, false);
                            @endphp
                            <tr class="hover:bg-[#F7F4EE]/50 transition-colors">
                                <td class="px-6 py-4">
                                    <a href="{{ route('pesanan.show', $pesanan) }}" class="font-heading font-semibold text-[#29302D] hover:text-[#A8754F] transition-colors">
                                        {{ $pesanan->nomor_pesanan }}
                                    </a>
                                    <p class="text-[11px] text-[#9A816B] mt-0.5">
                                        {{ $pesanan->item_pesanan_count }} item &middot; {{ $pesanan->tanggal_pesan->format('d M Y') }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-[#29302D]">{{ $pesanan->pelanggan->nama ?? '-' }}</p>
                                    <p class="text-[11px] text-[#9A816B]">{{ $pesanan->pelanggan->no_hp ?? '-' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-xs text-[#38443D]">{{ $pesanan->deadline->format('d M Y') }}</p>
                                    @if($pesanan->status === 'aktif')
                                        @if($sisaHari < 0)
                                            <p class="text-[11px] font-semibold text-[#B86B67]">Terlambat {{ abs($sisaHari) }} hari</p>
                                        @elseif($sisaHari <= 3)
                                            <p class="text-[11px] font-semibold text-[#C49A52]">{{ $sisaHari === 0 ? 'Hari ini' : 'H-'.$sisaHari }}</p>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-heading font-semibold text-[#A8754F] whitespace-nowrap">
                                    Rp {{ number_format((float) ($pesanan->total_tagihan ?? 0), 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4">
                                    @include('pesanan._badge', ['status' => $pesanan->status_bayar])
                                    @if((float) $pesanan->sisa_tagihan > 0)
                                        <p class="text-[11px] text-[#9A816B] mt-1">Sisa Rp {{ number_format((float) $pesanan->sisa_tagihan, 0, ',', '.') }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @include('pesanan._badge', ['status' => $pesanan->status])
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('pesanan.show', $pesanan) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-[#E7DED2] text-xs font-medium text-[#38443D] hover:bg-[#F7F4EE] transition-colors">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($pesananList->hasPages())
                <div class="px-6 py-4 border-t border-[#E7DED2]">
                    {{ $pesananList->links() }}
                </div>
            @endif
        @endif
    </div>

    @if($pesananList->total() > 0)
        <p class="mt-3 text-xs text-[#9A816B] text-right">
            Menampilkan {{ $pesananList->firstItem() }}–{{ $pesananList->lastItem() }} dari {{ $pesananList->total() }} pesanan
        </p>
    @endif
</x-app-layout>
