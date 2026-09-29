<x-app-layout title="Detail Pengukuran - {{ $pengukuran->pelanggan->nama ?? 'Pelanggan' }}">
    <x-slot name="header">
        Detail Ukuran Tubuh
    </x-slot>

    {{-- Tombol Kembali --}}
    <div class="mb-5">
        <a href="{{ route('pelanggan.show', $pengukuran->pelanggan_id) }}"
           class="inline-flex items-center gap-2 text-sm text-[#9A816B] hover:text-[#29302D] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Profil {{ $pengukuran->pelanggan->nama ?? 'Pelanggan' }}
        </a>
    </div>

    {{-- Header Info Pelanggan & Pengukuran --}}
    <div class="bg-white rounded-2xl border border-[#E7DED2] p-6 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-[#A8754F] text-white flex items-center justify-center font-heading font-bold text-2xl shrink-0">
                {{ strtoupper(substr($pengukuran->pelanggan->nama ?? 'P', 0, 1)) }}
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="font-heading font-bold text-xl text-[#29302D]">{{ $pengukuran->pelanggan->nama ?? 'Pelanggan' }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-semibold bg-[#F7F4EE] border border-[#E7DED2] text-[#38443D]">
                        Diukur: {{ $pengukuran->tanggal_ukur->format('d M Y') }}
                    </span>
                </div>
                <p class="text-xs text-[#9A816B] mt-0.5">{{ $pengukuran->pelanggan->no_hp ?? '-' }} &middot; {{ $pengukuran->pelanggan->alamat ?? 'Tidak ada alamat' }}</p>
            </div>
        </div>

        <div class="flex items-center gap-2 shrink-0">
            <a href="{{ route('pengukuran.edit', $pengukuran) }}"
               class="inline-flex items-center gap-1.5 px-4 py-2.5 rounded-xl border border-[#E7DED2] text-xs font-semibold text-[#38443D] hover:bg-[#F7F4EE] transition-colors">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Edit Ukuran
            </a>
            <form action="{{ route('pengukuran.destroy', $pengukuran) }}" method="POST"
                  onsubmit="return confirm('Hapus catatan pengukuran ini?')">
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

    {{-- Detail Grid Ukuran Tubuh --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-[#E7DED2] p-6">
                <h3 class="font-heading font-semibold text-base text-[#29302D] mb-4">Dimensi Tubuh (cm)</h3>

                @if(empty($pengukuran->data_ukuran))
                    <p class="text-xs text-[#9A816B]">Tidak ada data rincian ukuran yang dicatat.</p>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach($pengukuran->data_ukuran as $key => $val)
                            <div class="p-3 rounded-xl bg-[#F7F4EE]/60 border border-[#E7DED2]">
                                <p class="text-[11px] text-[#9A816B] capitalize">{{ str_replace('_', ' ', $key) }}</p>
                                <p class="font-heading font-bold text-lg text-[#29302D] mt-0.5">{{ $val }} <span class="text-xs font-normal text-[#9A816B]">cm</span></p>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        {{-- Kolom Kanan: Catatan Postur & Preferensi --}}
        <div>
            <div class="bg-white rounded-2xl border border-[#E7DED2] p-6">
                <p class="text-xs font-semibold text-[#9A816B] uppercase tracking-wide mb-3">Catatan Postur & Preferensi</p>
                @if($pengukuran->catatan)
                    <p class="text-xs text-[#38443D] leading-relaxed bg-[#F7F4EE]/60 border border-[#E7DED2] p-3 rounded-xl">
                        {{ $pengukuran->catatan }}
                    </p>
                @else
                    <p class="text-xs text-[#9A816B]">Tidak ada catatan khusus postur tubuh.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
