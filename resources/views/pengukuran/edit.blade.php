<x-app-layout title="Edit Pengukuran Pelanggan">
    <x-slot name="header">
        Edit Data Pengukuran
    </x-slot>

    {{-- Tombol Kembali --}}
    <div class="mb-5">
        <a href="{{ route('pengukuran.show', $pengukuran) }}"
           class="inline-flex items-center gap-2 text-sm font-medium text-zinc-500 hover:text-zinc-900 transition-colors">
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
        <div class="bg-white rounded-2xl border border-zinc-200 p-6 sm:p-8 shadow-2xs">
            <div class="mb-6 pb-4 border-b border-zinc-100">
                <h2 class="font-bold text-base text-zinc-900">Pelanggan: {{ $pengukuran->pelanggan->nama ?? 'Pelanggan' }}</h2>
                <p class="text-xs text-zinc-500 mt-0.5">{{ $pengukuran->pelanggan->no_hp ?? '-' }}</p>
            </div>

            <form action="{{ route('pengukuran.update', $pengukuran) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1.5">
                        Tanggal Pengukuran <span class="text-rose-600">*</span>
                    </label>
                    <input type="date" name="tanggal_ukur" value="{{ old('tanggal_ukur', $pengukuran->tanggal_ukur->format('Y-m-d')) }}" required
                           class="w-full max-w-xs px-3.5 py-2.5 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors">
                </div>

                {{-- Bagian Atas / Badan / Baju --}}
                <div class="space-y-3 pt-4 border-t border-zinc-100">
                    <h3 class="font-semibold text-sm text-zinc-900">Bagian Atas & Baju (cm)</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 mb-1">Lingkar Leher</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_leher]" value="{{ $curr['lingkar_leher'] ?? '' }}" placeholder="0"
                                   class="w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 mb-1">Lingkar Dada</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_dada]" value="{{ $curr['lingkar_dada'] ?? '' }}" placeholder="0"
                                   class="w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 mb-1">Lebar Bahu</label>
                            <input type="number" step="0.5" name="data_ukuran[lebar_bahu]" value="{{ $curr['lebar_bahu'] ?? '' }}" placeholder="0"
                                   class="w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 mb-1">Panjang Lengan</label>
                            <input type="number" step="0.5" name="data_ukuran[panjang_lengan]" value="{{ $curr['panjang_lengan'] ?? '' }}" placeholder="0"
                                   class="w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 mb-1">Kerung Lengan</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_kerung_lengan]" value="{{ $curr['lingkar_kerung_lengan'] ?? '' }}" placeholder="0"
                                   class="w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 mb-1">Panjang Baju</label>
                            <input type="number" step="0.5" name="data_ukuran[panjang_baju]" value="{{ $curr['panjang_baju'] ?? '' }}" placeholder="0"
                                   class="w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors">
                        </div>
                    </div>
                </div>

                {{-- Bagian Bawah / Celana / Rok --}}
                <div class="space-y-3 pt-4 border-t border-zinc-100">
                    <h3 class="font-semibold text-sm text-zinc-900">Bagian Bawah, Celana & Rok (cm)</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 mb-1">Lingkar Pinggang</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_pinggang]" value="{{ $curr['lingkar_pinggang'] ?? '' }}" placeholder="0"
                                   class="w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 mb-1">Lingkar Pinggul</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_pinggul]" value="{{ $curr['lingkar_pinggul'] ?? '' }}" placeholder="0"
                                   class="w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 mb-1">Panjang Celana / Rok</label>
                            <input type="number" step="0.5" name="data_ukuran[panjang_celana]" value="{{ $curr['panjang_celana'] ?? '' }}" placeholder="0"
                                   class="w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 mb-1">Lingkar Paha</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_paha]" value="{{ $curr['lingkar_paha'] ?? '' }}" placeholder="0"
                                   class="w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 mb-1">Lingkar Lutut</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_lutut]" value="{{ $curr['lingkar_lutut'] ?? '' }}" placeholder="0"
                                   class="w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 mb-1">Pesak / Selangkangan</label>
                            <input type="number" step="0.5" name="data_ukuran[pesak]" value="{{ $curr['pesak'] ?? '' }}" placeholder="0"
                                   class="w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors">
                        </div>
                    </div>
                </div>

                {{-- Catatan Postur / Preferensi Jahitan --}}
                <div class="pt-4 border-t border-zinc-100">
                    <label class="block text-sm font-medium text-zinc-700 mb-1.5">
                        Catatan Postur Tubuh & Preferensi (Opsional)
                    </label>
                    <textarea name="catatan" rows="3"
                              class="w-full px-3.5 py-2.5 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors resize-none">{{ old('catatan', $pengukuran->catatan) }}</textarea>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-3 border-t border-zinc-100">
                    <button type="submit"
                            class="px-5 py-2.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-medium transition-colors shadow-2xs cursor-pointer">
                        Perbarui Pengukuran
                    </button>
                    <a href="{{ route('pengukuran.show', $pengukuran) }}"
                       class="px-4 py-2.5 rounded-lg border border-zinc-200 text-sm font-medium text-zinc-700 hover:bg-zinc-50 transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
