<x-app-layout title="Edit Ukuran Pola">
    <x-slot name="header">
        Edit Ukuran Pola: {{ $polaDetail->jenisPakaian->nama_jenis }} ({{ $polaDetail->size }})
    </x-slot>

    {{-- Tombol Kembali --}}
    <div class="mb-5">
        <a href="{{ route('pola.show', $polaDetail->jenis_pakaian_id) }}"
           class="inline-flex items-center gap-2 text-sm text-[#9A816B] hover:text-[#29302D] transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke {{ $polaDetail->jenisPakaian->nama_jenis }}
        </a>
    </div>

    <div class="max-w-2xl">
        <div class="bg-white rounded-2xl border border-[#E7DED2] p-6 sm:p-8">
            <form action="{{ route('pola.detail.update', $polaDetail) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Size / Ukuran --}}
                <div>
                    <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
                        Pilih Ukuran / Size <span class="text-[#B86B67]">*</span>
                    </label>
                    <select name="size" required
                            class="w-full px-3 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        @foreach(['S', 'M', 'L', 'XL', 'XXL', 'Custom'] as $opt)
                            <option value="{{ $opt }}" {{ $polaDetail->size === $opt ? 'selected' : '' }}>
                                {{ $opt === 'Custom' ? 'Custom / Standar Khusus' : 'Size ' . $opt }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Panduan Dimensi Standar Pola (cm) --}}
                <div>
                    <p class="text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-3">
                        Dimensi Ukuran Pola (cm)
                    </p>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs text-[#9A816B] mb-1">Lingkar Dada</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_dada]"
                                   value="{{ $polaDetail->data_ukuran['lingkar_dada'] ?? '' }}"
                                   placeholder="Contoh: 96"
                                   class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs text-[#9A816B] mb-1">Panjang Baju / Pakaian</label>
                            <input type="number" step="0.5" name="data_ukuran[panjang_baju]"
                                   value="{{ $polaDetail->data_ukuran['panjang_baju'] ?? '' }}"
                                   placeholder="Contoh: 72"
                                   class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs text-[#9A816B] mb-1">Panjang Lengan</label>
                            <input type="number" step="0.5" name="data_ukuran[panjang_lengan]"
                                   value="{{ $polaDetail->data_ukuran['panjang_lengan'] ?? '' }}"
                                   placeholder="Contoh: 60"
                                   class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs text-[#9A816B] mb-1">Lebar Bahu</label>
                            <input type="number" step="0.5" name="data_ukuran[lebar_bahu]"
                                   value="{{ $polaDetail->data_ukuran['lebar_bahu'] ?? '' }}"
                                   placeholder="Contoh: 44"
                                   class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs text-[#9A816B] mb-1">Lingkar Pinggang</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_pinggang]"
                                   value="{{ $polaDetail->data_ukuran['lingkar_pinggang'] ?? '' }}"
                                   placeholder="Contoh: 82"
                                   class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-xs text-[#9A816B] mb-1">Lingkar Pinggul</label>
                            <input type="number" step="0.5" name="data_ukuran[lingkar_pinggul]"
                                   value="{{ $polaDetail->data_ukuran['lingkar_pinggul'] ?? '' }}"
                                   placeholder="Contoh: 98"
                                   class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                        </div>
                    </div>
                </div>

                {{-- Upload Foto Pola --}}
                {{-- Upload Foto Pola --}}
                <div x-data="{
                    previewUrl: null,
                    hasFoto: {{ $polaDetail->foto_pola ? 'true' : 'false' }},
                    deletingFoto: false,
                    resetPilihanFoto() {
                        this.previewUrl = null;
                        if (this.$refs.fotoInput) {
                            this.$refs.fotoInput.value = '';
                        }
                    },
                    async hapusFotoSekarang() {
                        if (!confirm('Hapus foto ilustrasi pola ini sekarang?')) return;
                        this.deletingFoto = true;
                        try {
                            const res = await fetch('{{ route('pola.detail.delete-foto', $polaDetail) }}', {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            });
                            if (res.ok) {
                                this.hasFoto = false;
                                this.resetPilihanFoto();
                            } else {
                                alert('Gagal menghapus foto.');
                            }
                        } catch (err) {
                            alert('Terjadi kesalahan koneksi.');
                        } finally {
                            this.deletingFoto = false;
                        }
                    }
                }">
                    <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
                        Foto Ilustrasi Pola / Sketsa
                    </label>

                    {{-- Card Foto Tersimpan di Server (dengan Tombol X) --}}
                    <template x-if="hasFoto">
                        <div class="mb-3 relative rounded-xl overflow-hidden border border-[#E7DED2] max-w-xs bg-[#F7F4EE]">
                            <img src="{{ asset('storage/' . $polaDetail->foto_pola) }}" class="w-full h-44 object-cover">
                            <button type="button"
                                    @click="hapusFotoSekarang()"
                                    :disabled="deletingFoto"
                                    class="absolute top-2 right-2 w-8 h-8 rounded-full bg-white/90 hover:bg-white text-[#B86B67] flex items-center justify-center border border-[#E7DED2] shadow-xs cursor-pointer transition-transform hover:scale-105"
                                    title="Hapus foto tersimpan dari server">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>

                    <div>
                        <label class="block text-[11px] text-[#9A816B] mb-1" x-text="hasFoto ? 'Ganti dengan foto baru:' : 'Pilih foto pola:'"></label>
                        <input type="file" x-ref="fotoInput" name="foto_pola" accept="image/*"
                               @change="previewUrl = ($event.target.files.length) ? URL.createObjectURL($event.target.files[0]) : null"
                               class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-xs focus:border-[#A8754F] focus:outline-none file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#29302D] file:text-white file:cursor-pointer">

                        {{-- Preview Foto yang Baru Dipilih (dengan Tombol X untuk Batal/Hapus Pilihan) --}}
                        <template x-if="previewUrl">
                            <div class="mt-3 relative rounded-xl overflow-hidden border border-[#E7DED2] max-w-xs bg-[#F7F4EE]">
                                <div class="px-3 py-1.5 bg-[#F7F4EE] border-b border-[#E7DED2] flex items-center justify-between">
                                    <p class="text-[10px] text-[#A8754F] font-semibold">Foto Baru Dipilih</p>
                                    <button type="button"
                                            @click="resetPilihanFoto()"
                                            class="w-5 h-5 rounded-full bg-white text-[#B86B67] flex items-center justify-center border border-[#E7DED2] cursor-pointer hover:bg-[#B86B67]/10"
                                            title="Batal pilih foto ini">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                                <img :src="previewUrl" class="w-full h-36 object-cover">
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Upload File Pola / Template PDF --}}
                <div x-data="{
                    hasFile: {{ $polaDetail->file_pola ? 'true' : 'false' }},
                    selectedFileName: null,
                    deletingFile: false,
                    resetPilihanFile() {
                        this.selectedFileName = null;
                        if (this.$refs.fileInput) {
                            this.$refs.fileInput.value = '';
                        }
                    },
                    async hapusFileSekarang() {
                        if (!confirm('Hapus file dokumen pola ini sekarang?')) return;
                        this.deletingFile = true;
                        try {
                            const res = await fetch('{{ route('pola.detail.delete-file', $polaDetail) }}', {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json'
                                }
                            });
                            if (res.ok) {
                                this.hasFile = false;
                                this.resetPilihanFile();
                            } else {
                                alert('Gagal menghapus file.');
                            }
                        } catch (err) {
                            alert('Terjadi kesalahan koneksi.');
                        } finally {
                            this.deletingFile = false;
                        }
                    }
                }">
                    <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
                        File Dokumen Pola / Template Potongan (PDF / Dokumen)
                    </label>

                    {{-- Card File Tersimpan di Server (dengan Tombol X) --}}
                    <template x-if="hasFile">
                        <div class="mb-3 p-3 rounded-xl border border-[#E7DED2] bg-[#F7F4EE]/60 max-w-sm flex items-center justify-between gap-3">
                            <div class="flex items-center gap-2 overflow-hidden">
                                <svg class="w-5 h-5 text-[#A8754F] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <span class="text-xs text-[#29302D] font-medium truncate">{{ basename($polaDetail->file_pola ?? '') }}</span>
                            </div>
                            <button type="button"
                                    @click="hapusFileSekarang()"
                                    :disabled="deletingFile"
                                    class="w-7 h-7 rounded-full bg-white hover:bg-[#B86B67]/10 text-[#B86B67] flex items-center justify-center border border-[#E7DED2] cursor-pointer transition-colors shrink-0"
                                    title="Hapus file tersimpan dari server">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </template>

                    <div>
                        <label class="block text-[11px] text-[#9A816B] mb-1" x-text="hasFile ? 'Ganti dengan file baru:' : 'Pilih file dokumen:'"></label>
                        <input type="file" x-ref="fileInput" name="file_pola" accept=".pdf,.jpg,.jpeg,.png"
                               @change="selectedFileName = ($event.target.files.length) ? $event.target.files[0].name : null"
                               class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-xs focus:border-[#A8754F] focus:outline-none file:mr-3 file:py-1 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-[#29302D] file:text-white file:cursor-pointer">

                        {{-- Preview File yang Baru Dipilih (dengan Tombol X untuk Batal/Hapus Pilihan) --}}
                        <template x-if="selectedFileName">
                            <div class="mt-2.5 p-2.5 rounded-xl border border-[#E7DED2] bg-[#F7F4EE] max-w-sm flex items-center justify-between gap-2">
                                <div class="flex items-center gap-2 overflow-hidden">
                                    <svg class="w-4 h-4 text-[#A8754F] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <span class="text-xs text-[#29302D] font-medium truncate" x-text="selectedFileName"></span>
                                </div>
                                <button type="button"
                                        @click="resetPilihanFile()"
                                        class="w-5 h-5 rounded-full bg-white hover:bg-[#B86B67]/10 text-[#B86B67] flex items-center justify-center border border-[#E7DED2] cursor-pointer shrink-0"
                                        title="Batal pilih file ini">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </template>

                        <p class="text-[11px] text-[#9A816B] mt-1">Format PDF atau gambar resolusi tinggi (maksimal 10MB).</p>
                    </div>
                </div>

                {{-- Catatan Khusus Pola --}}
                <div>
                    <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
                        Catatan Potongan / Tips Menjahit (Opsional)
                    </label>
                    <textarea name="catatan" rows="3"
                              class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none resize-none">{{ old('catatan', $polaDetail->catatan) }}</textarea>
                </div>

                {{-- Action Buttons --}}
                <div class="flex items-center gap-3 pt-3 border-t border-[#E7DED2]">
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-[#29302D] hover:bg-[#38443D] text-white text-xs font-semibold transition-all cursor-pointer">
                        Perbarui Pola
                    </button>
                    <a href="{{ route('pola.show', $polaDetail->jenis_pakaian_id) }}"
                       class="px-4 py-2.5 rounded-xl border border-[#E7DED2] text-xs font-semibold text-[#9A816B] hover:text-[#29302D] hover:bg-[#F7F4EE] transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
