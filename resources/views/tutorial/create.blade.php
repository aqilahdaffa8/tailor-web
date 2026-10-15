<x-app-layout title="Tambah Tutorial">
    <x-slot name="header">
        Tambah Tutorial
    </x-slot>

    <div class="max-w-3xl">
        <div class="bg-white rounded-2xl border border-[#E7DED2] p-6 sm:p-8">
            @include('tutorial._form', [
                'aksi' => route('tutorial.store'),
                'batal' => route('tutorial.index'),
            ])
        </div>
    </div>
</x-app-layout>
