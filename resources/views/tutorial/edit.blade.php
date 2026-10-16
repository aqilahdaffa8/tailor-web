<x-app-layout title="Ubah Tutorial">
    <x-slot name="header">
        Ubah Tutorial
    </x-slot>

    <div class="max-w-3xl">
        <div class="bg-white rounded-2xl border border-zinc-200 p-6 sm:p-8 shadow-xs">
            @include('tutorial._form', [
                'aksi' => route('tutorial.update', $tutorial),
                'batal' => route('tutorial.show', $tutorial),
            ])
        </div>
    </div>
</x-app-layout>
