<x-app-layout title="Detail Pelanggan">
    <x-slot name="header">
        Detail Pelanggan
    </x-slot>

    {{-- Tombol Kembali --}}
    <div class="mb-5">
        <a href="{{ route('pelanggan.index') }}"
           class="inline-flex items-center gap-2 text-sm font-medium text-zinc-500 hover:text-zinc-900 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar Pelanggan
        </a>
    </div>

    {{-- Header Card Pelanggan --}}
    <div class="bg-white rounded-2xl border border-zinc-200 p-6 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-2xs">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-full bg-zinc-100 border border-zinc-200 text-zinc-800 flex items-center justify-center font-bold text-lg shrink-0">
                {{ strtoupper(substr($pelanggan->nama, 0, 1)) }}
            </div>
            <div>
                <h2 class="font-bold text-lg sm:text-xl text-zinc-900 tracking-tight">{{ $pelanggan->nama }}</h2>
                <p class="text-sm text-zinc-500 mt-0.5">{{ $pelanggan->no_hp }}</p>
                @if($pelanggan->alamat)
                    <p class="text-xs text-zinc-500 mt-1 max-w-md leading-relaxed">{{ $pelanggan->alamat }}</p>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('pelanggan.edit', $pelanggan) }}"
               class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-zinc-200 text-xs font-medium text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900 transition-colors">
                <svg class="w-3.5 h-3.5 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </a>
            <form action="{{ route('pelanggan.destroy', $pelanggan) }}" method="POST"
                  onsubmit="return confirm('Hapus pelanggan ini? Tindakan ini tidak bisa dibatalkan.')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-lg border border-rose-200 text-xs font-medium text-rose-700 hover:bg-rose-50 transition-colors cursor-pointer">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Hapus
                </button>
            </form>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6">

        {{-- Kolom Kiri: Ringkasan & Catatan --}}
        <div class="space-y-5">
            <div class="bg-white rounded-2xl border border-zinc-200 p-5 shadow-2xs">
                <p class="text-xs font-medium text-zinc-500 mb-4">Ringkasan Pelanggan</p>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-zinc-500">Total Pesanan</p>
                        <p class="font-bold text-2xl text-zinc-900 mt-0.5 tabular-nums">{{ $pelanggan->pesanan->count() }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-zinc-500">Data Ukuran Tersimpan</p>
                        <p class="font-bold text-2xl text-zinc-900 mt-0.5 tabular-nums">{{ $pelanggan->pengukuran->count() }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-zinc-500">Bergabung Sejak</p>
                        <p class="text-sm font-medium text-zinc-700 mt-0.5">{{ $pelanggan->created_at->format('d M Y') }}</p>
                    </div>
                </div>
            </div>

            @if($pelanggan->catatan)
                <div class="bg-zinc-50 rounded-2xl border border-zinc-200 p-5 shadow-2xs">
                    <p class="text-xs font-medium text-zinc-700 mb-2">Catatan Internal</p>
                    <p class="text-sm text-zinc-600 leading-relaxed">{{ $pelanggan->catatan }}</p>
                </div>
            @endif
        </div>

        {{-- Kolom Kanan: Data Ukuran & Riwayat Pesanan --}}
        <div class="lg:col-span-3 space-y-6">
            {{-- Section Riwayat Ukuran Tubuh --}}
            <div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden shadow-2xs">
                <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-100">
                    <h3 class="font-semibold text-sm text-zinc-900">Ukuran Tubuh Pelanggan</h3>
                    <a href="{{ route('pengukuran.create', ['pelanggan_id' => $pelanggan->id]) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium transition-colors shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Ukur Baru
                    </a>
                </div>

                @if($pelanggan->pengukuran->isEmpty())
                    <div class="py-10 text-center">
                        <p class="text-xs text-zinc-500">Belum ada catatan ukuran tubuh untuk pelanggan ini.</p>
                    </div>
                @else
                    <div class="divide-y divide-zinc-100">
                        @foreach($pelanggan->pengukuran as $ukur)
                            <div class="px-6 py-4 flex flex-col sm:flex-row sm:items-center justify-between gap-3 hover:bg-zinc-50/60 transition-colors">
                                <div>
                                    <div class="flex items-center gap-2">
                                        <p class="font-semibold text-xs text-zinc-900">Tanggal Ukur: {{ $ukur->tanggal_ukur->format('d M Y') }}</p>
                                    </div>
                                    <div class="flex flex-wrap gap-1.5 mt-2 text-xs">
                                        @foreach(array_slice($ukur->data_ukuran ?? [], 0, 4, true) as $k => $v)
                                            <span class="px-2 py-0.5 rounded-md bg-zinc-100 border border-zinc-200 text-zinc-700 tabular-nums">
                                                {{ ucfirst(str_replace('_', ' ', $k)) }}: {{ $v }} cm
                                            </span>
                                        @endforeach
                                        @if(count($ukur->data_ukuran ?? []) > 4)
                                            <span class="px-2 py-0.5 rounded-md bg-white border border-zinc-200 text-zinc-500">
                                                +{{ count($ukur->data_ukuran) - 4 }} ukuran lainnya
                                            </span>
                                        @endif
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <a href="{{ route('pengukuran.show', $ukur) }}"
                                       class="px-3 py-1.5 rounded-lg border border-zinc-200 text-xs font-medium text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900 transition-colors">
                                        Detail Ukuran
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Riwayat Pesanan --}}
            <div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden shadow-2xs">
                <div class="flex items-center justify-between px-6 py-4 border-b border-zinc-100">
                    <h3 class="font-semibold text-sm text-zinc-900">Riwayat Pesanan</h3>
                    <a href="{{ route('pesanan.create', ['pelanggan_id' => $pelanggan->id]) }}"
                       class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium transition-colors shadow-2xs">
                        <svg class="w-3.5 h-3.5 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                        </svg>
                        Pesanan Baru
                    </a>
                </div>

                @if($pelanggan->pesanan->isEmpty())
                    <div class="py-14 text-center">
                        <p class="text-sm text-zinc-500">Belum ada pesanan untuk pelanggan ini</p>
                    </div>
                @else
                    <div class="divide-y divide-zinc-100">
                        @foreach($pelanggan->pesanan as $pesanan)
                            <div class="px-6 py-4 flex items-center justify-between gap-4 hover:bg-zinc-50/60 transition-colors">
                                <div>
                                    <a href="{{ route('pesanan.show', $pesanan) }}" class="font-semibold text-sm text-zinc-900 hover:text-zinc-600 transition-colors">{{ $pesanan->nomor_pesanan }}</a>
                                    <p class="text-xs text-zinc-500 mt-0.5">
                                        {{ $pesanan->item_pesanan_count ?? 0 }} item
                                        &middot; {{ $pesanan->created_at->format('d M Y') }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-3 shrink-0">
                                    @php
                                        $statusColors = [
                                            'aktif'      => 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20',
                                            'selesai'    => 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20',
                                            'diambil'    => 'bg-zinc-100 text-zinc-700 ring-1 ring-zinc-600/20',
                                            'dibatalkan' => 'bg-rose-50 text-rose-700 ring-1 ring-rose-600/20',
                                        ];
                                        $colorClass = $statusColors[$pesanan->status] ?? 'bg-zinc-100 text-zinc-700 ring-1 ring-zinc-600/20';
                                    @endphp
                                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                        {{ ucfirst($pesanan->status) }}
                                    </span>
                                    <span class="text-sm font-semibold text-zinc-900 tabular-nums">
                                        Rp {{ number_format($pesanan->total_harga, 0, ',', '.') }}
                                    </span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>
