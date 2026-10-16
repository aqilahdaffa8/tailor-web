<x-app-layout title="Tambah Ukuran Pola">
    <x-slot name="header">
        Tambah Ukuran Pola: {{ $jenisPakaian->nama_jenis }}
    </x-slot>

    @php
        $inputClass = 'w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 placeholder:text-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 transition-colors';
    @endphp

    {{-- Tombol Kembali --}}
    <div class="mb-5">
        <a href="{{ route('pola.show', $jenisPakaian) }}"
           class="inline-flex items-center gap-2 text-sm text-zinc-500 hover:text-zinc-900 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke {{ $jenisPakaian->nama_jenis }}
        </a>
    </div>

    <div class="max-w-2xl">
        <div class="bg-white rounded-2xl border border-zinc-200 p-6 sm:p-8 shadow-xs">
            <form action="{{ route('pola.detail.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                <input type="hidden" name="jenis_pakaian_id" value="{{ $jenisPakaian->id }}">

                {{-- Size / Ukuran --}}
                <div>
                    <label class="block text-xs font-medium text-zinc-700 mb-1">
                        Pilih Ukuran / Size <span class="text-rose-500">*</span>
                    </label>
                    <select name="size" required
                            class="{{ $inputClass }}">
                        <option value="S">Size S</option>
                        <option value="M" selected>Size M</option>
                        <option value="L">Size L</option>
                        <option value="XL">Size XL</option>
                        <option value="XXL">Size XXL</option>
                        <option value="Custom">Custom / Standar Khusus</option>
                    </select>
                </div>

                {{-- Panduan Dimensi Standar Pola (cm) --}}
                <div>
                    <p class="text-xs font-semibold text-zinc-700 uppercase tracking-wider mb-3">
                        Dimensi Ukuran Pola (cm)
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 mb-1">Lingkar Dada</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_dada]" placeholder="Contoh: 96"
                                   class="{{ $inputClass }} tabular-nums">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 mb-1">Panjang Baju / Pakaian</label>
                            <input type="number" step="0.5" name="data_ukuran[panjang_baju]" placeholder="Contoh: 72"
                                   class="{{ $inputClass }} tabular-nums">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 mb-1">Panjang Lengan</label>
                            <input type="number" step="0.5" name="data_ukuran[panjang_lengan]" placeholder="Contoh: 60"
                                   class="{{ $inputClass }} tabular-nums">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 mb-1">Lebar Bahu</label>
                            <input type="number" step="0.5" name="data_ukuran[lebar_bahu]" placeholder="Contoh: 44"
                                   class="{{ $inputClass }} tabular-nums">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 mb-1">Lingkar Pinggang</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_pinggang]" placeholder="Contoh: 82"
                                   class="{{ $inputClass }} tabular-nums">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-zinc-600 mb-1">Lingkar Pinggul</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_pinggul]" placeholder="Contoh: 98"
                                   class="{{ $inputClass }} tabular-nums">
                        </div>
                    </div>
                </div>

                {{-- Upload Foto Pola --}}
                <div x-data="{
                    previewUrl: null,
                    resetPilihanFoto() {
                        this.previewUrl = null;
                        if (this.$refs.fotoInput) {
                            this.$refs.fotoInput.value = '';
                        }
                    }
                }">
                    <label class="block text-xs font-medium text-zinc-700 mb-1">
                        Foto Ilustrasi Pola / Sketsa (Opsional)
                    </label>
                    <input type="file" x-ref="fotoInput" name="foto_pola" accept="image/*"
                           @change="previewUrl = ($event.target.files.length) ? URL.createObjectURL($event.target.files[0]) : null"
                           class="{{ $inputClass }} text-xs file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-zinc-100 file:text-zinc-700 file:cursor-pointer">

                    {{-- Preview Foto yang Baru Dipilih (dengan Tombol X) --}}
                    <template x-if="previewUrl">
                        <div class="mt-3 relative rounded-xl overflow-hidden border border-zinc-200 max-w-xs bg-zinc-50 shadow-xs">
                            <div class="px-3 py-1.5 bg-zinc-50 border-b border-zinc-200 flex items-center justify-between">
                                <p class="text-xs text-zinc-700 font-medium">Foto Dipilih</p>
                                <button type="button"
                                        @click="resetPilihanFoto()"
                                        class="w-5 h-5 rounded-full bg-white text-rose-600 flex items-center justify-center border border-zinc-200 cursor-pointer hover:bg-rose-50"
                                        title="Batal pilih foto ini">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                            <img :src="previewUrl" class="w-full h-40 object-cover">
                        </div>
                    </template>
                </div>

                {{-- Upload File Pola / Template PDF --}}
                <div x-data="{
                    selectedFileName: null,
                    resetPilihanFile() {
                        this.selectedFileName = null;
                        if (this.$refs.fileInput) {
                            this.$refs.fileInput.value = '';
                        }
                    }
                }">
                    <label class="block text-xs font-medium text-zinc-700 mb-1">
                        File Dokumen Pola / Template Potongan (PDF / Dokumen)
                    </label>
                    <input type="file" x-ref="fileInput" name="file_pola" accept=".pdf,.jpg,.jpeg,.png"
                           @change="selectedFileName = ($event.target.files.length) ? $event.target.files[0].name : null"
                           class="{{ $inputClass }} text-xs file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-medium file:bg-zinc-100 file:text-zinc-700 file:cursor-pointer">

                    {{-- Preview File yang Baru Dipilih (dengan Tombol X) --}}
                    <template x-if="selectedFileName">
                        <div class="mt-2.5 p-2.5 rounded-xl border border-zinc-200 bg-zinc-50 max-w-sm flex items-center justify-between gap-2 shadow-xs">
                            <div class="flex items-center gap-2 overflow-hidden">
                                <svg class="w-4 h-4 text-zinc-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="text-xs text-zinc-900 font-medium truncate" x-text="selectedFileName"></span>
                            </div>
                            <button type="button"
                                    @click="resetPilihanFile()"
                                    class="w-5 h-5 rounded-full bg-white hover:bg-rose-50 text-rose-600 flex items-center justify-center border border-zinc-200 cursor-pointer shrink-0"
                                    title="Batal pilih file ini">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>

                    <p class="text-xs text-zinc-500 mt-1">Format PDF atau gambar resolusi tinggi (maksimal 10MB).</p>
                </div>

                {{-- Catatan Khusus Pola --}}
                <div>
                    <label class="block text-xs font-medium text-zinc-700 mb-1">
                        Catatan Potongan / Tips Menjahit (Opsional)
                    </label>
                    <textarea name="catatan" rows="3"
                              placeholder="Misal: Tambahkan kampuh 2 cm pada kelim bawah, gunakan furing tipis..."
                              class="{{ $inputClass }} resize-none"></textarea>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-3 pt-3 border-t border-zinc-200">
                    <button type="submit"
                            class="px-5 py-2.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium transition-colors shadow-xs cursor-pointer">
                        Simpan Ukuran Pola
                    </button>
                    <a href="{{ route('pola.show', $jenisPakaian) }}"
                       class="px-4 py-2 rounded-lg text-xs font-medium text-zinc-600 hover:text-zinc-900 transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
