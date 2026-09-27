<x-app-layout title="Library Pola">
    <x-slot name="header">
        Library Pola Pakaian
    </x-slot>

    <div x-data="{ modalJenisOpen: false }">
        {{-- Search & Tombol Tambah --}}
        <div class="flex items-center gap-2 mb-6">
            <form method="GET" action="{{ route('pola.index') }}" class="flex items-center gap-2 flex-1 max-w-sm">
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Cari kategori atau jenis pakaian..."
                       class="w-full px-4 py-2.5 bg-white border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                <button type="submit"
                        class="px-4 py-2.5 bg-[#F7F4EE] border border-[#E7DED2] rounded-xl text-xs font-semibold text-[#38443D] hover:bg-[#E7DED2] transition-colors cursor-pointer shrink-0">
                    Cari
                </button>
                @if($search)
                    <a href="{{ route('pola.index') }}"
                       class="px-3 py-2.5 text-xs text-[#9A816B] hover:text-[#29302D] transition-colors shrink-0">
                        Reset
                    </a>
                @endif
            </form>

            <button type="button"
                    @click="modalJenisOpen = true"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#29302D] hover:bg-[#38443D] text-white text-xs font-semibold transition-all shrink-0 ml-auto cursor-pointer">
                <svg class="w-4 h-4 text-[#C49A52]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Jenis Pakaian
            </button>
        </div>

        {{-- Daftar Kategori & Jenis Pakaian --}}
        @if($kategoriList->isEmpty())
            <div class="bg-white rounded-2xl border border-[#E7DED2] p-12 text-center">
                <div class="w-16 h-16 rounded-full bg-[#F7F4EE] border border-[#E7DED2] flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-[#9A816B]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="font-heading font-semibold text-[#29302D]">Tidak ada data pola</p>
                <p class="text-xs text-[#9A816B] mt-1">
                    @if($search)
                        Tidak ada hasil untuk pencarian "{{ $search }}"
                    @else
                        Mulai dengan menambahkan jenis pakaian pada kategori yang tersedia.
                    @endif
                </p>
            </div>
        @else
            <div class="space-y-6">
                @foreach($kategoriList as $kategori)
                    <div class="bg-white rounded-2xl border border-[#E7DED2] overflow-hidden">
                        {{-- Header Kategori --}}
                        <div class="px-6 py-4 bg-[#F7F4EE]/60 border-b border-[#E7DED2] flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-[#A8754F] text-white flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr($kategori->nama_kategori, 0, 1)) }}
                                </div>
                                <div>
                                    <h3 class="font-heading font-bold text-sm text-[#29302D]">{{ $kategori->nama_kategori }}</h3>
                                    @if($kategori->deskripsi)
                                        <p class="text-[11px] text-[#9A816B]">{{ $kategori->deskripsi }}</p>
                                    @endif
                                </div>
                            </div>
                            <span class="text-xs font-semibold px-2.5 py-1 rounded-full bg-white border border-[#E7DED2] text-[#38443D]">
                                {{ $kategori->jenisPakaian->count() }} Model
                            </span>
                        </div>

                        {{-- Daftar Jenis Pakaian Dalam Kategori --}}
                        @if($kategori->jenisPakaian->isEmpty())
                            <div class="py-8 text-center text-xs text-[#9A816B]">
                                Belum ada jenis pakaian di kategori ini.
                            </div>
                        @else
                            <div class="divide-y divide-[#E7DED2]">
                                @foreach($kategori->jenisPakaian as $jenis)
                                    <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-[#F7F4EE]/40 transition-colors">
                                        <div class="flex items-start gap-3">
                                            <div class="w-10 h-10 rounded-full bg-[#F7F4EE] border border-[#E7DED2] flex items-center justify-center shrink-0 text-[#A8754F]">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.242 4.243 3 3 0 004.242-4.243zm0-5.758a3 3 0 10-4.242-4.243 3 3 0 004.242 4.243z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <a href="{{ route('pola.show', $jenis) }}" class="font-heading font-semibold text-sm text-[#29302D] hover:text-[#A8754F] transition-colors">
                                                    {{ $jenis->nama_jenis }}
                                                </a>
                                                <p class="text-xs text-[#9A816B] mt-0.5">
                                                    {{ $jenis->deskripsi ?? 'Tidak ada deskripsi.' }}
                                                </p>
                                                <div class="flex items-center gap-2 mt-2">
                                                    <span class="text-[11px] font-semibold text-[#A8754F]">
                                                        {{ $jenis->pola_detail_count }} Ukuran Pola Tersedia
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0">
                                            <a href="{{ route('pola.show', $jenis) }}"
                                               class="px-3.5 py-1.5 rounded-lg border border-[#E7DED2] text-xs font-semibold text-[#38443D] hover:bg-[#F7F4EE] transition-colors">
                                                Lihat Pola
                                            </a>
                                            <form action="{{ route('pola.jenis.destroy', $jenis) }}" method="POST"
                                                  onsubmit="return confirm('Hapus model pakaian {{ $jenis->nama_jenis }} beserta seluruh polanya?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="p-1.5 rounded-lg text-[#B86B67] hover:bg-[#B86B67]/10 transition-colors cursor-pointer"
                                                        title="Hapus Model">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                                    </svg>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        @endif

        {{-- Modal Tambah Jenis Pakaian --}}
        <div x-show="modalJenisOpen" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto bg-black/40 flex items-center justify-center p-4">
            <div @click.away="modalJenisOpen = false" 
                 class="bg-white rounded-3xl p-6 sm:p-8 max-w-md w-full border border-[#E7DED2]">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-[#E7DED2]">
                    <h3 class="font-heading font-bold text-base text-[#29302D]">Tambah Model Pakaian</h3>
                    <button type="button" @click="modalJenisOpen = false" class="text-[#9A816B] hover:text-[#29302D] cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form action="{{ route('pola.jenis.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
                            Kategori Pola <span class="text-[#B86B67]">*</span>
                        </label>
                        <select name="kategori_pola_id" required
                                class="w-full px-3 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($allKategori as $kat)
                                <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
                            Nama Model / Jenis <span class="text-[#B86B67]">*</span>
                        </label>
                        <input type="text" name="nama_jenis" required placeholder="Contoh: Kemeja Slim Fit Formal"
                               class="w-full px-3 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
                            Deskripsi (Opsional)
                        </label>
                        <textarea name="deskripsi" rows="2" placeholder="Catatan karakteristik model..."
                                  class="w-full px-3 py-2 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none resize-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-[#E7DED2]">
                        <button type="button" @click="modalJenisOpen = false"
                                class="px-4 py-2 rounded-xl text-xs font-semibold text-[#9A816B] hover:text-[#29302D] cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-xl bg-[#29302D] hover:bg-[#38443D] text-white text-xs font-semibold cursor-pointer">
                            Simpan Model
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
