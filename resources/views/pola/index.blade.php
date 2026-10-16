<x-app-layout title="Library Pola">
    <x-slot name="header">
        Library Pola Pakaian
    </x-slot>

    @php
        $inputClass = 'w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 placeholder:text-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900 transition-colors';
    @endphp

    <div x-data="{ modalJenisOpen: false }">
        {{-- Search & Tombol Tambah --}}
        <div class="flex items-center gap-2 mb-6">
            <form method="GET" action="{{ route('pola.index') }}" class="flex items-center gap-2 flex-1 max-w-sm">
                <input type="text" name="search" value="{{ $search }}"
                       placeholder="Cari kategori atau jenis pakaian..."
                       class="w-full px-3 py-2 bg-white border border-zinc-200 rounded-lg text-xs text-zinc-900 placeholder:text-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900">
                <button type="submit"
                        class="px-3.5 py-2 bg-white border border-zinc-200 rounded-lg text-xs font-medium text-zinc-700 hover:bg-zinc-50 transition-colors shadow-xs cursor-pointer shrink-0">
                    Cari
                </button>
                @if($search)
                    <a href="{{ route('pola.index') }}"
                       class="px-2.5 py-2 text-xs font-medium text-zinc-500 hover:text-zinc-900 transition-colors shrink-0">
                        Reset
                    </a>
                @endif
            </form>

            <button type="button"
                    @click="modalJenisOpen = true"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium transition-colors shadow-xs shrink-0 ml-auto cursor-pointer">
                <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Jenis Pakaian
            </button>
        </div>

        {{-- Daftar Kategori & Jenis Pakaian --}}
        @if($kategoriList->isEmpty())
            <div class="bg-white rounded-2xl border border-zinc-200 p-12 text-center shadow-xs">
                <div class="w-12 h-12 rounded-xl bg-zinc-100 flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <p class="font-semibold text-sm text-zinc-900">Tidak ada data pola</p>
                <p class="text-xs text-zinc-500 mt-1">
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
                    <div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden shadow-xs">
                        {{-- Header Kategori --}}
                        <div class="px-6 py-4 bg-zinc-50/60 border-b border-zinc-200 flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-zinc-900 text-white flex items-center justify-center font-bold text-xs">
                                    {{ strtoupper(substr($kategori->nama_kategori, 0, 1)) }}
                                </div>
                                <div>
                                    <h3 class="font-semibold text-sm text-zinc-900">{{ $kategori->nama_kategori }}</h3>
                                    @if($kategori->deskripsi)
                                        <p class="text-xs text-zinc-500 mt-0.5">{{ $kategori->deskripsi }}</p>
                                    @endif
                                </div>
                            </div>
                            <span class="text-xs font-medium px-2.5 py-0.5 rounded-full bg-zinc-100 border border-zinc-200 text-zinc-700">
                                {{ $kategori->jenisPakaian->count() }} Model
                            </span>
                        </div>

                        {{-- Daftar Jenis Pakaian Dalam Kategori --}}
                        @if($kategori->jenisPakaian->isEmpty())
                            <div class="py-8 text-center text-xs text-zinc-500">
                                Belum ada jenis pakaian di kategori ini.
                            </div>
                        @else
                            <div class="divide-y divide-zinc-200">
                                @foreach($kategori->jenisPakaian as $jenis)
                                    <div class="p-5 flex flex-col sm:flex-row sm:items-center justify-between gap-4 hover:bg-zinc-50/50 transition-colors">
                                        <div class="flex items-start gap-3">
                                            <div class="w-10 h-10 rounded-full bg-zinc-100 border border-zinc-200 flex items-center justify-center shrink-0 text-zinc-700">
                                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M14.121 14.121L19 19m-7-7l7-7m-7 7l-2.879 2.879M12 12L9.121 9.121m0 5.758a3 3 0 10-4.242 4.243 3 3 0 004.242-4.243zm0-5.758a3 3 0 10-4.242-4.243 3 3 0 004.242 4.243z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <a href="{{ route('pola.show', $jenis) }}" class="font-semibold text-sm text-zinc-900 hover:underline transition-colors">
                                                    {{ $jenis->nama_jenis }}
                                                </a>
                                                <p class="text-xs text-zinc-500 mt-0.5">
                                                    {{ $jenis->deskripsi ?? 'Tidak ada deskripsi.' }}
                                                </p>
                                                <div class="flex items-center gap-2 mt-2">
                                                    <span class="text-xs font-medium text-zinc-700">
                                                        {{ $jenis->pola_detail_count }} Ukuran Pola Tersedia
                                                    </span>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="flex items-center gap-2 shrink-0">
                                            <a href="{{ route('pola.show', $jenis) }}"
                                               class="px-3 py-1.5 rounded-lg border border-zinc-200 bg-white text-xs font-medium text-zinc-700 hover:bg-zinc-50 transition-colors shadow-xs">
                                                Lihat Pola
                                            </a>
                                            <form action="{{ route('pola.jenis.destroy', $jenis) }}" method="POST"
                                                  onsubmit="return confirm('Hapus model pakaian {{ $jenis->nama_jenis }} beserta seluruh polanya?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
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
             class="fixed inset-0 z-50 overflow-y-auto bg-zinc-900/60 flex items-center justify-center p-4">
            <div @click.away="modalJenisOpen = false" 
                 class="bg-white rounded-2xl p-6 sm:p-8 max-w-md w-full border border-zinc-200 shadow-xl">
                <div class="flex items-center justify-between pb-4 mb-4 border-b border-zinc-200">
                    <h3 class="font-semibold text-base text-zinc-900">Tambah Model Pakaian</h3>
                    <button type="button" @click="modalJenisOpen = false" class="text-zinc-400 hover:text-zinc-600 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>

                <form action="{{ route('pola.jenis.store') }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-zinc-700 mb-1">
                            Kategori Pola <span class="text-rose-500">*</span>
                        </label>
                        <select name="kategori_pola_id" required
                                class="{{ $inputClass }}">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($allKategori as $kat)
                                <option value="{{ $kat->id }}">{{ $kat->nama_kategori }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-700 mb-1">
                            Nama Model / Jenis <span class="text-rose-500">*</span>
                        </label>
                        <input type="text" name="nama_jenis" required placeholder="Contoh: Kemeja Slim Fit Formal"
                               class="{{ $inputClass }}">
                    </div>

                    <div>
                        <label class="block text-xs font-medium text-zinc-700 mb-1">
                            Deskripsi (Opsional)
                        </label>
                        <textarea name="deskripsi" rows="2" placeholder="Catatan karakteristik model..."
                                  class="{{ $inputClass }} resize-none"></textarea>
                    </div>

                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-zinc-200">
                        <button type="button" @click="modalJenisOpen = false"
                                class="px-4 py-2 rounded-lg text-xs font-medium text-zinc-600 hover:text-zinc-900 cursor-pointer">
                            Batal
                        </button>
                        <button type="submit"
                                class="px-5 py-2.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium shadow-xs cursor-pointer">
                            Simpan Model
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
