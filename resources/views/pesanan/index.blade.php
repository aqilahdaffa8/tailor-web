<x-app-layout title="Pesanan Jahit">
    <x-slot name="header">
        Pesanan Jahit
    </x-slot>

    @php
        $adaFilter = collect($filters)->filter()->isNotEmpty();
        $hariIni = today();
    @endphp

    {{-- Toolbar: Filter & Tambah Pesanan --}}
    <div class="flex flex-col xl:flex-row xl:items-center justify-between gap-3 mb-6">
        <form method="GET" action="{{ route('pesanan.index') }}" class="flex flex-wrap items-center gap-2 flex-1">
            <div class="relative w-full sm:w-64">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none text-zinc-400">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                    </svg>
                </div>
                <input type="text" name="search" value="{{ $filters['search'] }}"
                       placeholder="Cari no. pesanan / pelanggan..."
                       class="w-full pl-9 pr-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 transition-colors">
            </div>

            <select name="status"
                    class="px-3 py-2 bg-white border border-zinc-200 rounded-lg text-xs font-medium text-zinc-700 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors">
                <option value="">Semua Status</option>
                @foreach(['aktif' => 'Aktif', 'selesai' => 'Selesai', 'diambil' => 'Diambil', 'dibatalkan' => 'Dibatalkan'] as $nilai => $label)
                    <option value="{{ $nilai }}" @selected($filters['status'] === $nilai)>{{ $label }}</option>
                @endforeach
            </select>

            <select name="deadline"
                    class="px-3 py-2 bg-white border border-zinc-200 rounded-lg text-xs font-medium text-zinc-700 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors">
                <option value="">Semua Deadline</option>
                <option value="terlambat" @selected($filters['deadline'] === 'terlambat')>Terlambat</option>
                <option value="mendekati" @selected($filters['deadline'] === 'mendekati')>Mendekati (H-3)</option>
                <option value="hari_ini" @selected($filters['deadline'] === 'hari_ini')>Deadline Hari Ini</option>
            </select>

            <select name="pembayaran"
                    class="px-3 py-2 bg-white border border-zinc-200 rounded-lg text-xs font-medium text-zinc-700 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors">
                <option value="">Semua Pembayaran</option>
                <option value="belum_lunas" @selected($filters['pembayaran'] === 'belum_lunas')>Belum Lunas</option>
            </select>

            <select name="pelanggan_id"
                    class="px-3 py-2 bg-white border border-zinc-200 rounded-lg text-xs font-medium text-zinc-700 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors max-w-[12rem]">
                <option value="">Semua Pelanggan</option>
                @foreach($daftarPelanggan as $pelanggan)
                    <option value="{{ $pelanggan->id }}" @selected((int) $filters['pelanggan_id'] === $pelanggan->id)>{{ $pelanggan->nama }}</option>
                @endforeach
            </select>

            <button type="submit"
                    class="px-3.5 py-2 bg-zinc-100 hover:bg-zinc-200 border border-zinc-200 rounded-lg text-xs font-medium text-zinc-700 transition-colors cursor-pointer shrink-0">
                Terapkan
            </button>
            @if($adaFilter)
                <a href="{{ route('pesanan.index') }}" class="px-2.5 py-2 text-xs font-medium text-zinc-500 hover:text-zinc-900 transition-colors shrink-0">
                    Reset
                </a>
            @endif
        </form>

        <a href="{{ route('pesanan.create') }}"
           class="inline-flex items-center justify-center gap-2 px-4 py-2 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-medium transition-colors shadow-2xs shrink-0">
            <svg class="w-4 h-4 text-zinc-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            <span>Buat Pesanan Baru</span>
        </a>
    </div>

    {{-- Tabel Pesanan --}}
    <div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden shadow-2xs">
        @if($pesananList->isEmpty())
            <div class="flex flex-col items-center justify-center py-20 text-center">
                <div class="w-12 h-12 rounded-xl bg-zinc-100 border border-zinc-200 flex items-center justify-center mb-3">
                    <svg class="w-6 h-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <p class="font-semibold text-sm text-zinc-900">Belum ada pesanan</p>
                <p class="text-xs text-zinc-500 mt-1">
                    @if($adaFilter)
                        Tidak ada pesanan yang cocok dengan filter yang dipilih.
                    @else
                        Mulai buat pesanan jahit pertama untuk pelanggan Anda.
                    @endif
                </p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-zinc-200 bg-zinc-50/80">
                            <th class="text-left px-6 py-3.5 text-xs font-medium text-zinc-500">Nomor Pesanan</th>
                            <th class="text-left px-6 py-3.5 text-xs font-medium text-zinc-500">Pelanggan</th>
                            <th class="text-left px-6 py-3.5 text-xs font-medium text-zinc-500">Batas Waktu (Deadline)</th>
                            <th class="text-right px-6 py-3.5 text-xs font-medium text-zinc-500">Total Tagihan</th>
                            <th class="text-left px-6 py-3.5 text-xs font-medium text-zinc-500">Pembayaran</th>
                            <th class="text-left px-6 py-3.5 text-xs font-medium text-zinc-500">Status</th>
                            <th class="text-right px-6 py-3.5 text-xs font-medium text-zinc-500">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-100">
                        @foreach($pesananList as $pesanan)
                            @php
                                $sisaHari = (int) $hariIni->diffInDays($pesanan->deadline, false);
                            @endphp
                            <tr class="hover:bg-zinc-50/60 transition-colors">
                                <td class="px-6 py-4">
                                    <a href="{{ route('pesanan.show', $pesanan) }}" class="font-semibold text-sm text-zinc-900 hover:text-zinc-600 transition-colors block">
                                        {{ $pesanan->nomor_pesanan }}
                                    </a>
                                    <p class="text-xs text-zinc-500 mt-0.5">
                                        {{ $pesanan->item_pesanan_count }} item &middot; {{ $pesanan->tanggal_pesan->format('d M Y') }}
                                    </p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="font-medium text-zinc-900">{{ $pesanan->pelanggan->nama ?? '-' }}</p>
                                    <p class="text-xs text-zinc-500 mt-0.5">{{ $pesanan->pelanggan->no_hp ?? '-' }}</p>
                                </td>
                                <td class="px-6 py-4">
                                    <p class="text-xs text-zinc-700 font-medium">{{ $pesanan->deadline->format('d M Y') }}</p>
                                    @if($pesanan->status === 'aktif')
                                        @if($sisaHari < 0)
                                            <p class="text-xs font-semibold text-rose-600 mt-0.5">Terlambat {{ abs($sisaHari) }} hari</p>
                                        @elseif($sisaHari <= 3)
                                            <p class="text-xs font-semibold text-amber-600 mt-0.5">{{ $sisaHari === 0 ? 'Hari ini' : 'H-'.$sisaHari }}</p>
                                        @endif
                                    @endif
                                </td>
                                <td class="px-6 py-4 text-right font-semibold text-sm text-zinc-900 tabular-nums whitespace-nowrap">
                                    Rp {{ number_format((float) ($pesanan->total_tagihan ?? 0), 0, ',', '.') }}
                                </td>
                                <td class="px-6 py-4">
                                    @include('pesanan._badge', ['status' => $pesanan->status_bayar])
                                    @if((float) $pesanan->sisa_tagihan > 0)
                                        <p class="text-xs text-zinc-500 mt-1 tabular-nums">Sisa Rp {{ number_format((float) $pesanan->sisa_tagihan, 0, ',', '.') }}</p>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @include('pesanan._badge', ['status' => $pesanan->status])
                                </td>
                                <td class="px-6 py-4 text-right">
                                    <a href="{{ route('pesanan.show', $pesanan) }}"
                                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-200 text-xs font-medium text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900 transition-colors">
                                        Detail
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($pesananList->hasPages())
                <div class="px-6 py-4 border-t border-zinc-100">
                    {{ $pesananList->links() }}
                </div>
            @endif
        @endif
    </div>

    @if($pesananList->total() > 0)
        <p class="mt-3 text-xs text-zinc-500 text-right">
            Menampilkan {{ $pesananList->firstItem() }}–{{ $pesananList->lastItem() }} dari {{ $pesananList->total() }} pesanan
        </p>
    @endif
</x-app-layout>
