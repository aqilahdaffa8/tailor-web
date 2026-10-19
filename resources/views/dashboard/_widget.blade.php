@php
    $rupiah = fn ($angka) => 'Rp '.number_format((float) $angka, 0, ',', '.');
    $lunas = $ringkasan['pembayaran_belum_lunas'];
    $keuangan = $ringkasan['keuangan_bulan_ini'];
    $terbesar = max($keuangan['pendapatan'], $keuangan['pengeluaran'], 1);
    $persenPendapatan = round($keuangan['pendapatan'] / $terbesar * 100);
    $persenPengeluaran = round($keuangan['pengeluaran'] / $terbesar * 100);
    $angkaStok = fn ($nilai) => rtrim(rtrim(number_format((float) $nilai, 2, ',', '.'), '0'), ',');
@endphp

{{-- Baris 1: kartu status pesanan & stok (2 kolom 2 baris di HP, 4 kolom di desktop) --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-6">
    <a href="{{ route('pesanan.index', ['status' => 'aktif']) }}"
       class="block bg-white rounded-xl sm:rounded-2xl p-3.5 sm:p-6 border border-zinc-200 transition-colors hover:border-zinc-300 shadow-2xs">
        <div class="flex items-center justify-between">
            <span class="text-[11px] sm:text-xs font-medium text-zinc-500 leading-tight">Pesanan Aktif</span>
            <span class="p-1.5 sm:p-2 rounded-lg sm:rounded-xl bg-zinc-100 text-zinc-700 shrink-0">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </span>
        </div>
        <p class="mt-2.5 sm:mt-5 text-xl sm:text-3xl font-bold tracking-tight text-zinc-900 tabular-nums">{{ $ringkasan['pesanan_aktif'] }}</p>
        <p class="mt-0.5 sm:mt-1.5 text-[10px] sm:text-xs text-zinc-500 truncate">Sedang dalam pengerjaan</p>
    </a>

    <a href="{{ route('pesanan.index', ['deadline' => 'mendekati']) }}"
       class="block bg-white rounded-xl sm:rounded-2xl p-3.5 sm:p-6 border border-zinc-200 transition-colors hover:border-zinc-300 shadow-2xs">
        <div class="flex items-center justify-between">
            <span class="text-[11px] sm:text-xs font-medium text-zinc-500 leading-tight">Mendekati Deadline</span>
            <span class="p-1.5 sm:p-2 rounded-lg sm:rounded-xl bg-amber-50 text-amber-600 shrink-0">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
        </div>
        <p class="mt-2.5 sm:mt-5 text-xl sm:text-3xl font-bold tracking-tight text-zinc-900 tabular-nums">{{ $ringkasan['pesanan_mendekati_deadline'] }}</p>
        <p class="mt-0.5 sm:mt-1.5 text-[10px] sm:text-xs text-amber-600 font-medium truncate">Dalam 3 hari ke depan</p>
    </a>

    <a href="{{ route('pesanan.index', ['deadline' => 'terlambat']) }}"
       class="block bg-white rounded-xl sm:rounded-2xl p-3.5 sm:p-6 border border-zinc-200 transition-colors hover:border-zinc-300 shadow-2xs">
        <div class="flex items-center justify-between">
            <span class="text-[11px] sm:text-xs font-medium text-zinc-500 leading-tight">Terlambat</span>
            <span class="p-1.5 sm:p-2 rounded-lg sm:rounded-xl bg-rose-50 text-rose-600 shrink-0">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </span>
        </div>
        <p class="mt-2.5 sm:mt-5 text-xl sm:text-3xl font-bold tracking-tight {{ $ringkasan['pesanan_terlambat'] > 0 ? 'text-rose-600' : 'text-zinc-900' }} tabular-nums">{{ $ringkasan['pesanan_terlambat'] }}</p>
        <p class="mt-0.5 sm:mt-1.5 text-[10px] sm:text-xs text-rose-600 font-medium truncate">Melewati deadline</p>
    </a>

    <a href="{{ route('inventory.index', ['tab' => 'bahan', 'menipis' => 1]) }}"
       class="block bg-white rounded-xl sm:rounded-2xl p-3.5 sm:p-6 border border-zinc-200 transition-colors hover:border-zinc-300 shadow-2xs">
        <div class="flex items-center justify-between">
            <span class="text-[11px] sm:text-xs font-medium text-zinc-500 leading-tight">Stok Menipis</span>
            <span class="p-1.5 sm:p-2 rounded-lg sm:rounded-xl bg-zinc-100 text-zinc-700 shrink-0">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                </svg>
            </span>
        </div>
        <p class="mt-2.5 sm:mt-5 text-xl sm:text-3xl font-bold tracking-tight text-zinc-900 tabular-nums">{{ $ringkasan['stok_menipis'] }}</p>
        <p class="mt-0.5 sm:mt-1.5 text-[10px] sm:text-xs text-zinc-500 truncate">Perlu restock bahan</p>
    </a>
</div>

{{-- Baris 2: pembayaran & keuangan --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-3 sm:gap-6 mt-3 sm:mt-6">
    <a href="{{ route('pesanan.index', ['pembayaran' => 'belum_lunas']) }}"
       class="block bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-zinc-200 transition-colors hover:border-zinc-300 shadow-2xs">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-zinc-500">Belum Lunas</span>
            <span class="p-1.5 sm:p-2 rounded-lg sm:rounded-xl bg-rose-50 text-rose-600">
                <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
        </div>
        <p class="mt-3 sm:mt-5 text-xl sm:text-3xl font-bold tracking-tight text-zinc-900 tabular-nums">{{ $rupiah($lunas['total_sisa']) }}</p>
        <p class="mt-1 sm:mt-1.5 text-xs text-zinc-500">Sisa tagihan dari {{ $lunas['jumlah_pesanan'] }} pesanan</p>
    </a>

    <div class="lg:col-span-2 bg-white rounded-xl sm:rounded-2xl p-4 sm:p-6 border border-zinc-200 shadow-2xs">
        <div class="flex items-center justify-between">
            <span class="text-xs font-medium text-zinc-500">Pendapatan vs Pengeluaran</span>
            <span class="text-xs text-zinc-500">{{ $ringkasan['periode']['label'] }}</span>
        </div>

        <div class="mt-6 space-y-5">
            <div>
                <div class="flex items-center justify-between text-sm mb-2">
                    <span class="text-zinc-700 font-medium">Pendapatan</span>
                    <span class="font-bold text-zinc-900 tabular-nums">{{ $rupiah($keuangan['pendapatan']) }}</span>
                </div>
                <div class="h-2 rounded-full bg-zinc-100 overflow-hidden">
                    <div class="h-full rounded-full bg-zinc-900" style="width: {{ $persenPendapatan }}%"></div>
                </div>
            </div>
            <div>
                <div class="flex items-center justify-between text-sm mb-2">
                    <span class="text-zinc-700 font-medium">Pengeluaran</span>
                    <span class="font-bold text-zinc-600 tabular-nums">{{ $rupiah($keuangan['pengeluaran']) }}</span>
                </div>
                <div class="h-2 rounded-full bg-zinc-100 overflow-hidden">
                    <div class="h-full rounded-full bg-zinc-400" style="width: {{ $persenPengeluaran }}%"></div>
                </div>
            </div>
        </div>

        <div class="mt-6 pt-5 border-t border-zinc-100 flex items-center justify-between text-sm">
            <span class="text-zinc-500">Selisih bulan ini</span>
            <span class="font-bold tabular-nums {{ $keuangan['selisih'] < 0 ? 'text-rose-600' : 'text-zinc-900' }}">
                {{ $keuangan['selisih'] < 0 ? '-' : '' }}{{ $rupiah(abs($keuangan['selisih'])) }}
            </span>
        </div>
    </div>
</div>

{{-- Baris 3: daftar singkat --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mt-6">
    {{-- Pesanan terlambat --}}
    <div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden shadow-2xs">
        <div class="px-6 py-4 border-b border-zinc-100 flex items-center justify-between">
            <h3 class="font-semibold text-sm text-zinc-900">Pesanan Terlambat</h3>
            <a href="{{ route('pesanan.index', ['deadline' => 'terlambat']) }}" class="text-xs font-medium text-zinc-600 hover:text-zinc-900 hover:underline">Lihat semua</a>
        </div>
        <div class="px-6 divide-y divide-zinc-100">
            @forelse($pesananTerlambat as $pesanan)
                <a href="{{ route('pesanan.show', $pesanan) }}"
                   class="flex items-center justify-between gap-4 py-3.5 group">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-zinc-900 group-hover:text-zinc-700 transition-colors truncate">{{ $pesanan->nomor_pesanan }}</p>
                        <p class="text-xs text-zinc-500 mt-0.5 truncate">{{ $pesanan->pelanggan->nama ?? '-' }}</p>
                    </div>
                    <span class="text-xs font-semibold text-rose-600 whitespace-nowrap">
                        {{ abs((int) today()->diffInDays($pesanan->deadline, false)) }} hari
                    </span>
                </a>
            @empty
                <p class="py-8 text-center text-xs text-zinc-400">Tidak ada pesanan yang terlambat.</p>
            @endforelse
        </div>
    </div>

    {{-- Pesanan mendekati deadline --}}
    <div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden shadow-2xs">
        <div class="px-6 py-4 border-b border-zinc-100 flex items-center justify-between">
            <h3 class="font-semibold text-sm text-zinc-900">Mendekati Deadline</h3>
            <a href="{{ route('pesanan.index', ['deadline' => 'mendekati']) }}" class="text-xs font-medium text-zinc-600 hover:text-zinc-900 hover:underline">Lihat semua</a>
        </div>
        <div class="px-6 divide-y divide-zinc-100">
            @forelse($pesananMendekati as $pesanan)
                @php $sisaHari = (int) today()->diffInDays($pesanan->deadline, false); @endphp
                <a href="{{ route('pesanan.show', $pesanan) }}"
                   class="flex items-center justify-between gap-4 py-3.5 group">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-zinc-900 group-hover:text-zinc-700 transition-colors truncate">{{ $pesanan->nomor_pesanan }}</p>
                        <p class="text-xs text-zinc-500 mt-0.5 truncate">{{ $pesanan->pelanggan->nama ?? '-' }}</p>
                    </div>
                    <span class="text-xs font-semibold text-amber-600 whitespace-nowrap">
                        {{ $sisaHari === 0 ? 'Hari ini' : 'H-'.$sisaHari }}
                    </span>
                </a>
            @empty
                <p class="py-8 text-center text-xs text-zinc-400">Tidak ada deadline dalam 3 hari ke depan.</p>
            @endforelse
        </div>
    </div>

    {{-- Stok menipis --}}
    <div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden shadow-2xs">
        <div class="px-6 py-4 border-b border-zinc-100 flex items-center justify-between">
            <h3 class="font-semibold text-sm text-zinc-900">Stok Bahan Menipis</h3>
            <a href="{{ route('inventory.index', ['tab' => 'bahan', 'menipis' => 1]) }}" class="text-xs font-medium text-zinc-600 hover:text-zinc-900 hover:underline">Lihat semua</a>
        </div>
        <div class="px-6 divide-y divide-zinc-100">
            @forelse($stokMenipis as $bahan)
                <a href="{{ route('inventory.index', ['tab' => 'bahan', 'search' => $bahan->nama_bahan]) }}"
                   class="flex items-center justify-between gap-4 py-3.5 group">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-zinc-900 group-hover:text-zinc-700 transition-colors truncate">{{ $bahan->nama_bahan }}</p>
                        <p class="text-xs text-zinc-500 mt-0.5">Minimum {{ $angkaStok($bahan->stok_minimum) }} {{ $bahan->satuan }}</p>
                    </div>
                    <span class="text-xs font-semibold text-zinc-900 whitespace-nowrap tabular-nums">
                        {{ $angkaStok($bahan->stok) }} {{ $bahan->satuan }}
                    </span>
                </a>
            @empty
                <p class="py-8 text-center text-xs text-zinc-400">Semua stok bahan masih aman.</p>
            @endforelse
        </div>
    </div>
</div>
