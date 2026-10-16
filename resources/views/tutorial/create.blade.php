<x-app-layout title="Tambah Tutorial">
    <x-slot name="header">
        Tambah Tutorial
    </x-slot>

    <div class="max-w-3xl">
        <div class="bg-white rounded-2xl border border-zinc-200 p-6 sm:p-8 shadow-xs">
            @include('tutorial._form', [
                'aksi' => route('tutorial.store'),
                'batal' => route('tutorial.index'),
            ])
        </div>
    </div>
</x-app-layout>
