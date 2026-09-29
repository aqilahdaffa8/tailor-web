<x-app-layout title="Edit Pengukuran Pelanggan">
    <x-slot name="header">
        Edit Data Pengukuran
    </x-slot>

    {{-- Tombol Kembali --}}
    <div class="mb-5">
        <a href="{{ route('pengukuran.show', $pengukuran) }}"
           class="inline-flex items-center gap-2 text-sm text-[#9A816B] hover:text-[#29302D] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Detail Ukuran
        </a>
    </div>

    @php
        $curr = $pengukuran->data_ukuran ?? [];
    @endphp

    <div class="max-w-3xl">
        <div class="bg-white rounded-2xl border border-[#E7DED2] p-6 sm:p-8">
            <div class="mb-6 pb-4 border-b border-[#E7DED2]">
                <h2 class="font-heading font-bold text-base text-[#29302D]">Pelanggan: {{ $pengukuran->pelanggan->nama ?? 'Pelanggan' }}</h2>
                <p class="text-xs text-[#9A816B]">{{ $pengukuran->pelanggan->no_hp ?? '-' }}</p>
            </div>

            <form action="{{ route('pengukuran.update', $pengukuran) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
                        Tanggal Pengukuran <span class="text-[#B86B67]">*</span>
                    </label>
                    <input type="date" name="tanggal_ukur" value="{{ old('tanggal_ukur', $pengukuran->tanggal_ukur->format('Y-m-d')) }}" required
                           class="w-full max-w-xs px-3 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                </div>

                {{-- Bagian Atas / Badan / Baju --}}
                <div class="space-y-3 pt-2 border-t border-[#E7DED2]">
                    <h3 class="font-heading font-semibold text-sm text-[#29302D]">Bagian Atas & Baju (cm)</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] text-[#9A816B] mb-1">Lingkar Leher</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_leher]" value="{{ $curr['lingkar_leher'] ?? '' }}" placeholder="0"
                                   class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] text-[#9A816B] mb-1">Lingkar Dada</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_dada]" value="{{ $curr['lingkar_dada'] ?? '' }}" placeholder="0"
                                   class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] text-[#9A816B] mb-1">Lebar Bahu</label>
                            <input type="number" step="0.5" name="data_ukuran[lebar_bahu]" value="{{ $curr['lebar_bahu'] ?? '' }}" placeholder="0"
                                   class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] text-[#9A816B] mb-1">Panjang Lengan</label>
                            <input type="number" step="0.5" name="data_ukuran[panjang_lengan]" value="{{ $curr['panjang_lengan'] ?? '' }}" placeholder="0"
                                   class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] text-[#9A816B] mb-1">Kerung Lengan</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_kerung_lengan]" value="{{ $curr['lingkar_kerung_lengan'] ?? '' }}" placeholder="0"
                                   class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] text-[#9A816B] mb-1">Panjang Baju</label>
                            <input type="number" step="0.5" name="data_ukuran[panjang_baju]" value="{{ $curr['panjang_baju'] ?? '' }}" placeholder="0"
                                   class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                    </div>
                </div>

                {{-- Bagian Bawah / Celana / Rok --}}
                <div class="space-y-3 pt-2 border-t border-[#E7DED2]">
                    <h3 class="font-heading font-semibold text-sm text-[#29302D]">Bagian Bawah, Celana & Rok (cm)</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] text-[#9A816B] mb-1">Lingkar Pinggang</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_pinggang]" value="{{ $curr['lingkar_pinggang'] ?? '' }}" placeholder="0"
                                   class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] text-[#9A816B] mb-1">Lingkar Pinggul</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_pinggul]" value="{{ $curr['lingkar_pinggul'] ?? '' }}" placeholder="0"
                                   class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] text-[#9A816B] mb-1">Panjang Celana / Rok</label>
                            <input type="number" step="0.5" name="data_ukuran[panjang_celana]" value="{{ $curr['panjang_celana'] ?? '' }}" placeholder="0"
                                   class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] text-[#9A816B] mb-1">Lingkar Paha</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_paha]" value="{{ $curr['lingkar_paha'] ?? '' }}" placeholder="0"
                                   class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] text-[#9A816B] mb-1">Lingkar Lutut</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_lutut]" value="{{ $curr['lingkar_lutut'] ?? '' }}" placeholder="0"
                                   class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] text-[#9A816B] mb-1">Pesak / Selangkangan</label>
                            <input type="number" step="0.5" name="data_ukuran[pesak]" value="{{ $curr['pesak'] ?? '' }}" placeholder="0"
                                   class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                    </div>
                </div>

                {{-- Catatan Postur / Preferensi Jahitan --}}
                <div class="pt-2 border-t border-[#E7DED2]">
                    <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
                        Catatan Postur Tubuh & Preferensi (Opsional)
                    </label>
                    <textarea name="catatan" rows="3"
                              class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none resize-none">{{ old('catatan', $pengukuran->catatan) }}</textarea>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-3 border-t border-[#E7DED2]">
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-[#29302D] hover:bg-[#38443D] text-white text-xs font-semibold transition-all cursor-pointer">
                        Perbarui Pengukuran
                    </button>
                    <a href="{{ route('pengukuran.show', $pengukuran) }}"
                       class="px-4 py-2.5 rounded-xl border border-[#E7DED2] text-xs font-semibold text-[#9A816B] hover:text-[#29302D] hover:bg-[#F7F4EE] transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
