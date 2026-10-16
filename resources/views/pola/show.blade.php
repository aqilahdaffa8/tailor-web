<x-app-layout title="Detail Pola - {{ $jenisPakaian->nama_jenis }}">
    <x-slot name="header">
        Detail Pola: {{ $jenisPakaian->nama_jenis }}
    </x-slot>

    {{-- Tombol Kembali --}}
    <div class="mb-5">
        <a href="{{ route('pola.index') }}"
           class="inline-flex items-center gap-2 text-sm text-zinc-500 hover:text-zinc-900 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Library Pola
        </a>
    </div>

    {{-- Header Model Pakaian --}}
    <div class="bg-white rounded-2xl border border-zinc-200 p-6 mb-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-xs">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-zinc-900 text-white flex items-center justify-center font-bold text-2xl shrink-0">
                {{ strtoupper(substr($jenisPakaian->nama_jenis, 0, 1)) }}
            </div>
            <div>
                <div class="flex items-center gap-2">
                    <h2 class="font-bold text-xl text-zinc-900">{{ $jenisPakaian->nama_jenis }}</h2>
                    <span class="px-2.5 py-0.5 rounded-full text-xs font-medium bg-zinc-100 border border-zinc-200 text-zinc-700">
                        {{ $jenisPakaian->kategoriPola->nama_kategori }}
                    </span>
                </div>
                <p class="text-xs text-zinc-500 mt-1">{{ $jenisPakaian->deskripsi ?? 'Tidak ada catatan deskripsi model.' }}</p>
            </div>
        </div>

        <div class="flex items-center gap-3 shrink-0">
            <a href="{{ route('pola.detail.create', $jenisPakaian) }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium transition-colors shadow-xs">
                <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Tambah Ukuran / Size Baru
            </a>
        </div>
    </div>

    {{-- Daftar Pola Berdasarkan Size --}}
    @if($jenisPakaian->polaDetail->isEmpty())
        <div class="bg-white rounded-2xl border border-zinc-200 p-12 text-center shadow-xs">
            <div class="w-12 h-12 rounded-xl bg-zinc-100 flex items-center justify-center mx-auto mb-3">
                <svg class="w-6 h-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <p class="font-semibold text-sm text-zinc-900">Belum ada ukuran pola</p>
            <p class="text-xs text-zinc-500 mt-1 mb-5">Tambahkan data ukuran pola (S, M, L, XL, atau Custom) beserta panduan potongannya.</p>
            <a href="{{ route('pola.detail.create', $jenisPakaian) }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium transition-colors shadow-xs">
                + Tambah Ukuran Pertama
            </a>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($jenisPakaian->polaDetail as $pola)
                <div class="bg-white rounded-2xl border border-zinc-200 overflow-hidden shadow-xs flex flex-col justify-between">
                    <div>
                        {{-- Card Header: Size & Actions --}}
                        <div class="p-5 border-b border-zinc-200 flex items-center justify-between bg-zinc-50/60">
                            <div class="flex items-center gap-2">
                                <span class="w-8 h-8 rounded-full bg-zinc-900 text-white font-bold text-xs flex items-center justify-center">
                                    {{ $pola->size }}
                                </span>
                                <span class="text-xs font-semibold text-zinc-900">Ukuran {{ $pola->size }}</span>
                            </div>

                            <div class="flex items-center gap-1">
                                <a href="{{ route('pola.detail.edit', $pola) }}"
                                   class="p-1.5 rounded-lg text-zinc-600 hover:bg-zinc-100 transition-colors"
                                   title="Edit Ukuran">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <form action="{{ route('pola.detail.destroy', $pola) }}" method="POST"
                                      onsubmit="return confirm('Hapus pola ukuran {{ $pola->size }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit"
                                            class="p-1.5 rounded-lg text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                            title="Hapus Ukuran">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Card Body: Foto Pola Preview & Data Ukuran --}}
                        <div class="p-5 space-y-4">
                            @if($pola->foto_pola)
                                <div class="rounded-xl overflow-hidden border border-zinc-200 bg-zinc-50">
                                    <img src="{{ asset('storage/' . $pola->foto_pola) }}" 
                                         alt="Foto Pola {{ $pola->size }}" 
                                         class="w-full h-44 object-cover">
                                </div>
                            @endif

                            {{-- Tabel Dimensi Ukuran --}}
                            @if(!empty($pola->data_ukuran) && is_array($pola->data_ukuran))
                                <div>
                                    <p class="text-xs font-semibold text-zinc-700 uppercase tracking-wider mb-2">Dimensi Ukuran (cm)</p>
                                    <div class="grid grid-cols-2 gap-2 text-xs">
                                        @foreach($pola->data_ukuran as $k => $v)
                                            <div class="p-2 rounded-lg bg-zinc-50 border border-zinc-200 flex justify-between">
                                                <span class="text-zinc-500 capitalize">{{ str_replace('_', ' ', $k) }}</span>
                                                <span class="font-semibold text-zinc-900 tabular-nums">{{ $v }} cm</span>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Catatan --}}
                            @if($pola->catatan)
                                <div>
                                    <p class="text-xs font-semibold text-zinc-700 uppercase tracking-wider mb-1">Catatan Pengerjaan</p>
                                    <p class="text-xs text-zinc-600 leading-relaxed">{{ $pola->catatan }}</p>
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Card Footer: File Download Link --}}
                    @if($pola->file_pola)
                        <div class="p-4 border-t border-zinc-200 bg-zinc-50/40">
                            <a href="{{ asset('storage/' . $pola->file_pola) }}" target="_blank"
                               class="inline-flex items-center gap-2 text-xs font-medium text-zinc-900 hover:underline">
                                <svg class="w-4 h-4 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                Unduh File Pola / Template Potongan
                            </a>
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
    @endif
</x-app-layout>
