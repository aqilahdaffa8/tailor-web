@php
    $labelTipe = ['artikel' => 'Artikel', 'video' => 'Video', 'pdf' => 'PDF'];
@endphp

<x-app-layout :title="$tutorial->judul">
    <x-slot name="header">
        Tutorial
    </x-slot>

    <div class="max-w-3xl">
        <a href="{{ route('tutorial.index') }}" class="text-xs text-[#9A816B] hover:text-[#29302D] transition-colors">&larr; Kembali ke daftar</a>

        <div class="bg-white rounded-2xl border border-[#E7DED2] p-6 sm:p-8 mt-3">
            <div class="flex items-start justify-between gap-4">
                <div>
                    <p class="text-[11px] font-semibold text-[#9A816B] uppercase tracking-wide">
                        {{ $tutorial->kategori }} &middot; {{ $labelTipe[$tutorial->tipe] }}
                    </p>
                    <h2 class="font-heading font-bold text-xl text-[#29302D] mt-1">{{ $tutorial->judul }}</h2>
                </div>
                <div class="flex items-center gap-2 shrink-0">
                    <a href="{{ route('tutorial.edit', $tutorial) }}"
                       class="px-3 py-2 rounded-xl border border-[#E7DED2] text-xs font-semibold text-[#38443D] hover:bg-[#F7F4EE] transition-colors">Ubah</a>
                    <form action="{{ route('tutorial.destroy', $tutorial) }}" method="POST"
                          onsubmit="return confirm('Hapus tutorial ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit"
                                class="px-3 py-2 rounded-xl border border-[#B86B67]/40 text-xs font-semibold text-[#B86B67] hover:bg-[#B86B67]/10 transition-colors cursor-pointer">Hapus</button>
                    </form>
                </div>
            </div>

            <div class="mt-6 space-y-5">
                @if($tutorial->tipe === 'video')
                    @if($tutorial->embed_url)
                        <div class="aspect-video rounded-xl overflow-hidden border border-[#E7DED2]">
                            <iframe src="{{ $tutorial->embed_url }}" title="{{ $tutorial->judul }}"
                                    class="w-full h-full" allowfullscreen loading="lazy"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    referrerpolicy="strict-origin-when-cross-origin"></iframe>
                        </div>
                    @else
                        <p class="text-sm text-[#B86B67]">Tautan video tidak valid. Perbarui tautan YouTube pada menu Ubah.</p>
                    @endif
                @endif

                @if($tutorial->tipe === 'pdf')
                    <a href="{{ route('tutorial.unduh', $tutorial) }}"
                       class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#29302D] hover:bg-[#38443D] text-white text-xs font-semibold transition-all">
                        <svg class="w-4 h-4 text-[#C49A52]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                        </svg>
                        Unduh PDF
                    </a>
                @endif

                @if($tutorial->tipe === 'artikel' && $tutorial->file_url)
                    <img src="{{ $tutorial->file_url }}" alt="{{ $tutorial->judul }}"
                         class="w-full max-h-96 object-cover rounded-xl border border-[#E7DED2]">
                @endif

                @if($tutorial->konten)
                    <div class="text-sm leading-relaxed text-[#29302D]">{!! nl2br(e($tutorial->konten)) !!}</div>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
