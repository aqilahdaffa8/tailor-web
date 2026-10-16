<x-app-layout title="Catat Pengukuran Pelanggan">
    <x-slot name="header">
        Catat Pengukuran Tubuh
    </x-slot>

    {{-- Tombol Kembali --}}
    <div class="mb-5">
        <a href="{{ $selectedPelanggan ? route('pelanggan.show', $selectedPelanggan) : route('pengukuran.index') }}"
           class="inline-flex items-center gap-2 text-sm font-medium text-zinc-500 hover:text-zinc-900 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            {{ $selectedPelanggan ? 'Kembali ke ' . $selectedPelanggan->nama : 'Kembali ke Daftar Pengukuran' }}
        </a>
    </div>

    @php
        $defaultUkuran = $latestUkuran ? ($latestUkuran->data_ukuran ?? []) : [];
    @endphp

    <div class="max-w-3xl" x-data="{
        ukuran: {{ json_encode($defaultUkuran) }},
        gunakanUkuranTerakhir(data) {
            this.ukuran = Object.assign({}, data);
        }
    }">
        <div class="bg-white rounded-2xl border border-zinc-200 p-6 sm:p-8 shadow-2xs">
            <form action="{{ route('pengukuran.store') }}" method="POST" class="space-y-6">
                @csrf

                {{-- Pilih Pelanggan & Tanggal Ukur --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">
                            Pilih Pelanggan <span class="text-rose-600">*</span>
                        </label>
                        <select name="pelanggan_id" required
                                class="w-full px-3.5 py-2.5 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors">
                            <option value="">-- Pilih Pelanggan --</option>
                            @foreach($daftarPelanggan as $p)
                                <option value="{{ $p->id }}" {{ ($selectedPelanggan && $selectedPelanggan->id == $p->id) ? 'selected' : '' }}>
                                    {{ $p->nama }} ({{ $p->no_hp }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-zinc-700 mb-1.5">
                            Tanggal Pengukuran <span class="text-rose-600">*</span>
                        </label>
                        <input type="date" name="tanggal_ukur" value="{{ old('tanggal_ukur', date('Y-m-d')) }}" required
                               class="w-full px-3.5 py-2.5 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors">
                    </div>
                </div>

                {{-- Banner Opsi Pre-Fill Pengukuran Terakhir --}}
                @if($latestUkuran)
                    <div class="p-4 rounded-xl bg-zinc-50 border border-zinc-200 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold text-zinc-900">Tersedia Pengukuran Sebelumnya</p>
                            <p class="text-xs text-zinc-500 mt-0.5">Terakhir diukur pada {{ $latestUkuran->tanggal_ukur->format('d M Y') }}.</p>
                        </div>
                        <button type="button"
                                @click="gunakanUkuranTerakhir({{ json_encode($latestUkuran->data_ukuran ?? []) }})"
                                class="px-3.5 py-1.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium cursor-pointer shrink-0 transition-colors shadow-2xs">
                            Gunakan Data Ukuran Terakhir
                        </button>
                    </div>
                @endif

                {{-- Bagian Atas / Badan / Baju --}}
                <div class="space-y-3 pt-4 border-t border-zinc-100">
                    <h3 class="font-semibold text-sm text-zinc-900">Bagian Atas & Baju (cm)</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 mb-1">Lingkar Leher</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_leher]" x-model="ukuran.lingkar_leher" placeholder="0"
                                   class="w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 mb-1">Lingkar Dada</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_dada]" x-model="ukuran.lingkar_dada" placeholder="0"
                                   class="w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 mb-1">Lebar Bahu</label>
                            <input type="number" step="0.5" name="data_ukuran[lebar_bahu]" x-model="ukuran.lebar_bahu" placeholder="0"
                                   class="w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 mb-1">Panjang Lengan</label>
                            <input type="number" step="0.5" name="data_ukuran[panjang_lengan]" x-model="ukuran.panjang_lengan" placeholder="0"
                                   class="w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 mb-1">Kerung Lengan</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_kerung_lengan]" x-model="ukuran.lingkar_kerung_lengan" placeholder="0"
                                   class="w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 mb-1">Panjang Baju</label>
                            <input type="number" step="0.5" name="data_ukuran[panjang_baju]" x-model="ukuran.panjang_baju" placeholder="0"
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
                            <input type="number" step="0.5" name="data_ukuran[lingkar_pinggang]" x-model="ukuran.lingkar_pinggang" placeholder="0"
                                   class="w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 mb-1">Lingkar Pinggul</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_pinggul]" x-model="ukuran.lingkar_pinggul" placeholder="0"
                                   class="w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 mb-1">Panjang Celana / Rok</label>
                            <input type="number" step="0.5" name="data_ukuran[panjang_celana]" x-model="ukuran.panjang_celana" placeholder="0"
                                   class="w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 mb-1">Lingkar Paha</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_paha]" x-model="ukuran.lingkar_paha" placeholder="0"
                                   class="w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 mb-1">Lingkar Lutut</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_lutut]" x-model="ukuran.lingkar_lutut" placeholder="0"
                                   class="w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 tabular-nums focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 mb-1">Pesak / Selangkangan</label>
                            <input type="number" step="0.5" name="data_ukuran[pesak]" x-model="ukuran.pesak" placeholder="0"
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
                              placeholder="Misal: Bahu kiri agak turun 1 cm, suka baju yang longgar (loose fit)..."
                              class="w-full px-3.5 py-2.5 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors resize-none"></textarea>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-3 border-t border-zinc-100">
                    <button type="submit"
                            class="px-5 py-2.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-medium transition-colors shadow-2xs cursor-pointer">
                        Simpan Pengukuran
                    </button>
                    <a href="{{ $selectedPelanggan ? route('pelanggan.show', $selectedPelanggan) : route('pengukuran.index') }}"
                       class="px-4 py-2.5 rounded-lg border border-zinc-200 text-sm font-medium text-zinc-700 hover:bg-zinc-50 transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
