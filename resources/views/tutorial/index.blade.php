@php
    $labelTipe = ['artikel' => 'Artikel', 'video' => 'Video', 'pdf' => 'PDF'];
    $warnaTipe = [
        'artikel' => 'bg-[#6F8A78]/15 text-[#38443D]',
        'video' => 'bg-[#B86B67]/15 text-[#B86B67]',
        'pdf' => 'bg-[#C49A52]/20 text-[#A8754F]',
    ];
    $adaFilter = $kategori || $tipe || $search;
@endphp

<x-app-layout title="Tutorial">
    <x-slot name="header">
        Tutorial & Panduan
    </x-slot>

    {{-- Filter & Tambah --}}
    <div class="flex flex-wrap items-center gap-2 mb-6">
        <form method="GET" action="{{ route('tutorial.index') }}" class="flex flex-wrap items-center gap-2 flex-1">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari judul atau isi..."
                   class="w-full sm:w-64 px-4 py-2.5 bg-white border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none">
            <select name="kategori"
                    class="px-3 py-2.5 bg-white border border-[#E7DED2] rounded-xl text-xs focus:border-[#A8754F] focus:outline-none">
                <option value="">Semua Kategori</option>
                @foreach($kategoriList as $kat)
                    <option value="{{ $kat }}" {{ $kategori === $kat ? 'selected' : '' }}>{{ $kat }}</option>
                @endforeach
            </select>
            <select name="tipe"
                    class="px-3 py-2.5 bg-white border border-[#E7DED2] rounded-xl text-xs focus:border-[#A8754F] focus:outline-none">
                <option value="">Semua Tipe</option>
                @foreach($labelTipe as $nilai => $label)
                    <option value="{{ $nilai }}" {{ $tipe === $nilai ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit"
                    class="px-4 py-2.5 bg-[#F7F4EE] border border-[#E7DED2] rounded-xl text-xs font-semibold text-[#38443D] hover:bg-[#E7DED2] transition-colors cursor-pointer">
                Terapkan
            </button>
            @if($adaFilter)
                <a href="{{ route('tutorial.index') }}" class="px-3 py-2.5 text-xs text-[#9A816B] hover:text-[#29302D] transition-colors">Reset</a>
            @endif
        </form>

        <a href="{{ route('tutorial.create') }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#29302D] hover:bg-[#38443D] text-white text-xs font-semibold transition-all shrink-0 ml-auto">
            <svg class="w-4 h-4 text-[#C49A52]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Tutorial
        </a>
    </div>

    @if($tutorial->isEmpty())
        <div class="bg-white rounded-2xl border border-[#E7DED2] flex flex-col items-center justify-center py-20 text-center">
            <p class="font-heading font-semibold text-[#29302D]">Belum ada tutorial</p>
            <p class="text-xs text-[#9A816B] mt-1">
                {{ $adaFilter ? 'Tidak ada tutorial yang cocok dengan filter.' : 'Mulai tulis panduan pertama Anda.' }}
            </p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($tutorial as $item)
                <a href="{{ route('tutorial.show', $item) }}"
                   class="bg-white rounded-2xl border border-[#E7DED2] p-5 hover:border-[#A8754F] transition-colors flex flex-col">
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <span class="text-[11px] font-semibold text-[#9A816B] uppercase tracking-wide truncate">{{ $item->kategori }}</span>
                        <span class="px-2 py-0.5 rounded-lg text-[10px] font-semibold shrink-0 {{ $warnaTipe[$item->tipe] }}">{{ $labelTipe[$item->tipe] }}</span>
                    </div>
                    <h3 class="font-heading font-semibold text-sm text-[#29302D]">{{ $item->judul }}</h3>
                    @if($item->konten)
                        <p class="text-xs text-[#9A816B] mt-2">{{ \Illuminate\Support\Str::limit($item->konten, 120) }}</p>
                    @endif
                </a>
            @endforeach
        </div>

        @if($tutorial->hasPages())
            <div class="mt-6">
                {{ $tutorial->links() }}
            </div>
        @endif
    @endif
</x-app-layout>
