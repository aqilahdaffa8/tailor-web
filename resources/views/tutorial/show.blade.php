@php
    $labelTipe = ['artikel' => 'Artikel', 'video' => 'Video', 'pdf' => 'PDF'];
@endphp

<x-app-layout :title="$tutorial->judul">
    <x-slot name="header">
        Tutorial
    </x-slot>

    <div class="max-w-3xl">
        <a href="{{ route('tutorial.index') }}" class="inline-flex items-center gap-2 text-sm text-zinc-500 hover:text-zinc-900 transition-colors">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
            Kembali ke Daftar Tutorial
        </a>

        <div class="bg-white rounded-2xl border border-zinc-200 p-6 sm:p-8 mt-4 shadow-xs">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-xs font-medium text-zinc-500 uppercase tracking-wider">
                        {{ $tutorial->kategori }} &middot; {{ $labelTipe[$tutorial->tipe] }}
                    </p>
                    <h2 class="font-bold text-xl text-zinc-900 mt-1">{{ $tutorial->judul }}</h2>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('tutorial.edit', $tutorial) }}"
                       class="px-3.5 py-1.5 rounded-lg border border-zinc-200 bg-white text-xs font-medium text-zinc-700 hover:bg-zinc-50 transition-colors shadow-xs">Ubah</a>
                    <form action="{{ route('tutorial.destroy', $tutorial) }}" method="POST"
                          onsubmit="return confirm('Hapus tutorial ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-3.5 py-1.5 rounded-lg border border-rose-200 bg-white text-xs font-medium text-rose-700 hover:bg-rose-50 transition-colors shadow-xs cursor-pointer">Hapus</button>
                    </form>
                </div>
            </div>

            <div class="mt-6 space-y-5">
                @if($tutorial->tipe === 'video')
                    @if($tutorial->embed_url)
                        <div class="aspect-video rounded-xl overflow-hidden border border-zinc-200 shadow-xs">
                            <iframe src="{{ $tutorial->embed_url }}" title="{{ $tutorial->judul }}"
                                    class="w-full h-full" allowfullscreen loading="lazy"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    referrerpolicy="strict-origin-when-cross-origin"></iframe>
                        </div>
                    @else
                        <p class="text-sm text-rose-600">Tautan video tidak valid. Perbarui tautan YouTube pada menu Ubah.</p>
                    @endif
                @endif

                @if($tutorial->tipe === 'pdf')
                    <a href="{{ route('tutorial.unduh', $tutorial) }}"
                       class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium transition-colors shadow-xs">
                        <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Unduh PDF
                    </a>
                @endif

                @if($tutorial->tipe === 'artikel' && $tutorial->file_url)
                    <img src="{{ $tutorial->file_url }}" alt="{{ $tutorial->judul }}"
                         class="w-full max-h-96 object-cover rounded-xl border border-zinc-200 shadow-xs">
                @endif

                @if($tutorial->konten)
                    <div class="text-sm leading-relaxed text-zinc-800 space-y-3">{!! nl2br(e($tutorial->konten)) !!}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
