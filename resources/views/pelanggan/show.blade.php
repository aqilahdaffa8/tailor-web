<x-app-layout title="Detail Pelanggan">
    <x-slot name="header">
        Detail Pelanggan
    </x-slot>

    {{-- Tombol Kembali --}}
    <div class="mb-5">
        <a href="{{ route('pelanggan.index') }}"
           class="inline-flex items-center gap-2 text-sm text-[#9A816B] hover:text-[#29302D] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar Pelanggan
        </a>
    </div>

    {{-- Header Card Pelanggan --}}
    <div class="bg-white rounded-2xl border border-[#E7DED2] p-6 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-[#A8754F] text-white flex items-center justify-center font-heading font-bold text-2xl shrink-0">
                {{ strtoupper(substr($pelanggan->nama, 0, 1)) }}
            </div>
            <div>
                <h2 class="font-heading font-bold text-xl text-[#29302D]">{{ $pelanggan->nama }}</h2>
                <p class="text-sm text-[#9A816B] mt-0.5">{{ $pelanggan->no_hp }}</p>
                @if($pelanggan->alamat)
                    <p class="text-xs text-[#9A816B] mt-1 max-w-sm">{{ $pelanggan->alamat }}</p>
                @endif
            </div>
        </div>
        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('pelanggan.edit', $pelanggan) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-[#E7DED2] text-xs font-semibold text-[#38443D] hover:bg-[#F7F4EE] transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit
            </a>
            <form action="{{ route('pelanggan.destroy', $pelanggan) }}" method="POST"
                  onsubmit="return confirm('Hapus pelanggan ini? Tindakan ini tidak bisa dibatalkan.')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-[#B86B67]/30 text-xs font-semibold text-[#B86B67] hover:bg-[#B86B67]/10 transition-colors cursor-pointer">
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
        <div class="space-y-4">
            <div class="bg-white rounded-2xl border border-[#E7DED2] p-5">
                <p class="text-xs font-semibold text-[#9A816B] uppercase tracking-wide mb-4">Ringkasan</p>
                <div class="space-y-4">
                    <div>
                        <p class="text-xs text-[#9A816B]">Total Pesanan</p>
                        <p class="font-heading font-bold text-2xl text-[#29302D] mt-0.5">{{ $pelanggan->pesanan->count() }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-[#9A816B]">Data Ukuran</p>
                        <p class="font-heading font-bold text-2xl text-[#29302D] mt-0.5">{{ $pelanggan->pengukuran->count() }}</p>
                    </div>
                    <div>
                        <p class="text-xs text-[#9A816B]">Bergabung</p>
                        <p class="text-sm font-medium text-[#38443D] mt-0.5">{{ $pelanggan->created_at->format('d M Y') }}</p>
                    </div>
                </div>
            </div>

            @if($pelanggan->catatan)
                <div class="bg-[#C49A52]/10 rounded-2xl border border-[#C49A52]/30 p-5">
                    <p class="text-xs font-semibold text-[#A8754F] uppercase tracking-wide mb-2">Catatan Internal</p>
                    <p class="text-sm text-[#38443D] leading-relaxed">{{ $pelanggan->catatan }}</p>
                </div>
            @endif
        </div>

        {{-- Kolom Kanan: Riwayat Pesanan --}}
        <div class="lg:col-span-3">
            <div class="bg-white rounded-2xl border border-[#E7DED2] overflow-hidden">
                <div class="flex items-center justify-between px-6 py-4 border-b border-[#E7DED2]">
                    <h3 class="font-heading font-semibold text-[#29302D]">Riwayat Pesanan</h3>
                </div>

                @if($pelanggan->pesanan->isEmpty())
                    <div class="py-14 text-center">
                        <p class="text-sm text-[#9A816B]">Belum ada pesanan untuk pelanggan ini</p>
                    </div>
                @else
                    <div class="divide-y divide-[#E7DED2]">
                        @foreach($pelanggan->pesanan as $pesanan)
                            <div class="px-6 py-4 flex items-center justify-between gap-4 hover:bg-[#F7F4EE]/50 transition-colors">
                                <div>
                                    <p class="font-medium text-sm text-[#29302D]">{{ $pesanan->no_pesanan }}</p>
                                    <p class="text-xs text-[#9A816B] mt-0.5">
                                        {{ $pesanan->item_pesanan_count ?? 0 }} item
                                        &middot; {{ $pesanan->created_at->format('d M Y') }}
                                    </p>
                                </div>
                                <div class="flex items-center gap-3 shrink-0">
                                    @php
                                        $statusColors = [
                                            'Baru'    => 'bg-[#C49A52]/15 text-[#A8754F]',
                                            'Proses'  => 'bg-[#6F8A78]/15 text-[#6F8A78]',
                                            'Selesai' => 'bg-[#38443D]/10 text-[#38443D]',
                                            'Batal'   => 'bg-[#B86B67]/15 text-[#B86B67]',
                                        ];
                                        $colorClass = $statusColors[$pesanan->status] ?? 'bg-[#F7F4EE] text-[#9A816B]';
                                    @endphp
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $colorClass }}">
                                        {{ $pesanan->status }}
                                    </span>
                                    <span class="text-sm font-heading font-semibold text-[#A8754F]">
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
