@php
    $labelTipe = ['artikel' => 'Artikel', 'video' => 'Video', 'pdf' => 'PDF'];
    $warnaTipe = [
        'artikel' => 'bg-zinc-100 text-zinc-700 border border-zinc-200',
        'video' => 'bg-rose-50 text-rose-700 border border-rose-200/60',
        'pdf' => 'bg-amber-50 text-amber-700 border border-amber-200/60',
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
                   class="w-full sm:w-64 px-3 py-2 bg-white border border-zinc-200 rounded-lg text-xs text-zinc-900 placeholder:text-zinc-400 focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900">
            <select name="kategori"
                    class="px-3 py-2 bg-white border border-zinc-200 rounded-lg text-xs text-zinc-900 focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900">
                <option value="">Semua Kategori</option>
                @foreach($kategoriList as $kat)
                    <option value="{{ $kat }}" {{ $kategori === $kat ? 'selected' : '' }}>{{ $kat }}</option>
                @endforeach
            </select>
            <select name="tipe"
                    class="px-3 py-2 bg-white border border-zinc-200 rounded-lg text-xs text-zinc-900 focus:outline-none focus:ring-1 focus:ring-zinc-900 focus:border-zinc-900">
                <option value="">Semua Tipe</option>
                @foreach($labelTipe as $nilai => $label)
                    <option value="{{ $nilai }}" {{ $tipe === $nilai ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit"
                    class="px-3.5 py-2 bg-white border border-zinc-200 rounded-lg text-xs font-medium text-zinc-700 hover:bg-zinc-50 transition-colors shadow-xs cursor-pointer">
                Terapkan
            </button>
            @if($adaFilter)
                <a href="{{ route('tutorial.index') }}" class="px-2.5 py-2 text-xs font-medium text-zinc-500 hover:text-zinc-900 transition-colors">Reset</a>
            @endif
        </form>

        <a href="{{ route('tutorial.create') }}"
           class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium transition-colors shadow-xs shrink-0 ml-auto">
            <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Tutorial
        </a>
    </div>

    @if($tutorial->isEmpty())
        <div class="bg-white rounded-2xl border border-zinc-200 flex flex-col items-center justify-center py-20 text-center shadow-xs">
            <div class="w-12 h-12 rounded-xl bg-zinc-100 flex items-center justify-center mb-3">
                <svg class="w-6 h-6 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <p class="font-semibold text-sm text-zinc-900">Belum ada tutorial</p>
            <p class="text-xs text-zinc-500 mt-1">
                {{ $adaFilter ? 'Tidak ada tutorial yang cocok dengan filter.' : 'Mulai tulis panduan pertama Anda.' }}
            </p>
        </div>
    @else
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4">
            @foreach($tutorial as $item)
                <a href="{{ route('tutorial.show', $item) }}"
                   class="bg-white rounded-2xl border border-zinc-200 p-5 hover:border-zinc-400 transition-colors flex flex-col shadow-xs">
                    <div class="flex items-center justify-between gap-2 mb-3">
                        <span class="text-xs font-medium text-zinc-500 uppercase tracking-wider truncate">{{ $item->kategori }}</span>
                        <span class="px-2 py-0.5 rounded-full text-xs font-medium shrink-0 {{ $warnaTipe[$item->tipe] }}">{{ $labelTipe[$item->tipe] }}</span>
                    </div>
                    <h3 class="font-semibold text-sm text-zinc-900">{{ $item->judul }}</h3>
                    @if($item->konten)
                        <p class="text-xs text-zinc-500 mt-2 leading-relaxed">{{ \Illuminate\Support\Str::limit($item->konten, 120) }}</p>
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
