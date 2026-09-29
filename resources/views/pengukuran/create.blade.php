<x-app-layout title="Catat Pengukuran Pelanggan">
    <x-slot name="header">
        Catat Pengukuran Tubuh
    </x-slot>

    {{-- Tombol Kembali --}}
    <div class="mb-5">
        <a href="{{ $selectedPelanggan ? route('pelanggan.show', $selectedPelanggan) : route('pengukuran.index') }}"
           class="inline-flex items-center gap-2 text-sm text-[#9A816B] hover:text-[#29302D] transition-colors">
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
        <div class="bg-white rounded-2xl border border-[#E7DED2] p-6 sm:p-8">
            <form action="{{ route('pengukuran.store') }}" method="POST" class="space-y-6">
                @csrf

                {{-- Pilih Pelanggan & Tanggal Ukur --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
                            Pilih Pelanggan <span class="text-[#B86B67]">*</span>
                        </label>
                        <select name="pelanggan_id" required
                                class="w-full px-3 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                            <option value="">-- Pilih Pelanggan --</option>
                            @foreach($daftarPelanggan as $p)
                                <option value="{{ $p->id }}" {{ ($selectedPelanggan && $selectedPelanggan->id == $p->id) ? 'selected' : '' }}>
                                    {{ $p->nama }} ({{ $p->no_hp }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
                            Tanggal Pengukuran <span class="text-[#B86B67]">*</span>
                        </label>
                        <input type="date" name="tanggal_ukur" value="{{ old('tanggal_ukur', date('Y-m-d')) }}" required
                               class="w-full px-3 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                    </div>
                </div>

                {{-- Banner Opsi Pre-Fill Pengukuran Terakhir --}}
                @if($latestUkuran)
                    <div class="p-4 rounded-xl bg-[#F7F4EE] border border-[#E7DED2] flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                        <div>
                            <p class="text-xs font-semibold text-[#29302D]">Tersedia Pengukuran Sebelumnya</p>
                            <p class="text-[11px] text-[#9A816B]">Terakhir diukur pada {{ $latestUkuran->tanggal_ukur->format('d M Y') }}.</p>
                        </div>
                        <button type="button"
                                @click="gunakanUkuranTerakhir({{ json_encode($latestUkuran->data_ukuran ?? []) }})"
                                class="px-3.5 py-1.5 rounded-lg bg-[#29302D] hover:bg-[#38443D] text-white text-xs font-semibold cursor-pointer shrink-0 transition-all">
                            Gunakan Data Ukuran Terakhir
                        </button>
                    </div>
                @endif

                {{-- Bagian Atas / Badan / Baju --}}
                <div class="space-y-3 pt-2 border-t border-[#E7DED2]">
                    <h3 class="font-heading font-semibold text-sm text-[#29302D]">Bagian Atas & Baju (cm)</h3>
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        <div>
                            <label class="block text-[11px] text-[#9A816B] mb-1">Lingkar Leher</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_leher]" x-model="ukuran.lingkar_leher" placeholder="0"
                                   class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] text-[#9A816B] mb-1">Lingkar Dada</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_dada]" x-model="ukuran.lingkar_dada" placeholder="0"
                                   class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] text-[#9A816B] mb-1">Lebar Bahu</label>
                            <input type="number" step="0.5" name="data_ukuran[lebar_bahu]" x-model="ukuran.lebar_bahu" placeholder="0"
                                   class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] text-[#9A816B] mb-1">Panjang Lengan</label>
                            <input type="number" step="0.5" name="data_ukuran[panjang_lengan]" x-model="ukuran.panjang_lengan" placeholder="0"
                                   class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] text-[#9A816B] mb-1">Kerung Lengan</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_kerung_lengan]" x-model="ukuran.lingkar_kerung_lengan" placeholder="0"
                                   class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] text-[#9A816B] mb-1">Panjang Baju</label>
                            <input type="number" step="0.5" name="data_ukuran[panjang_baju]" x-model="ukuran.panjang_baju" placeholder="0"
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
                            <input type="number" step="0.5" name="data_ukuran[lingkar_pinggang]" x-model="ukuran.lingkar_pinggang" placeholder="0"
                                   class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] text-[#9A816B] mb-1">Lingkar Pinggul</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_pinggul]" x-model="ukuran.lingkar_pinggul" placeholder="0"
                                   class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] text-[#9A816B] mb-1">Panjang Celana / Rok</label>
                            <input type="number" step="0.5" name="data_ukuran[panjang_celana]" x-model="ukuran.panjang_celana" placeholder="0"
                                   class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] text-[#9A816B] mb-1">Lingkar Paha</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_paha]" x-model="ukuran.lingkar_paha" placeholder="0"
                                   class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] text-[#9A816B] mb-1">Lingkar Lutut</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_lutut]" x-model="ukuran.lingkar_lutut" placeholder="0"
                                   class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-[11px] text-[#9A816B] mb-1">Pesak / Selangkangan</label>
                            <input type="number" step="0.5" name="data_ukuran[pesak]" x-model="ukuran.pesak" placeholder="0"
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
                              placeholder="Misal: Bahu kiri agak turun 1 cm, suka baju yang longgar (loose fit)..."
                              class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none resize-none"></textarea>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-3 border-t border-[#E7DED2]">
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-[#29302D] hover:bg-[#38443D] text-white text-xs font-semibold transition-all cursor-pointer">
                        Simpan Pengukuran
                    </button>
                    <a href="{{ $selectedPelanggan ? route('pelanggan.show', $selectedPelanggan) : route('pengukuran.index') }}"
                       class="px-4 py-2.5 rounded-xl border border-[#E7DED2] text-xs font-semibold text-[#9A816B] hover:text-[#29302D] hover:bg-[#F7F4EE] transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
