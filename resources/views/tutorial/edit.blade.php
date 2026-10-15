<x-app-layout title="Ubah Tutorial">
    <x-slot name="header">
        Ubah Tutorial
    </x-slot>

    <div class="max-w-3xl">
        <div class="bg-white rounded-2xl border border-[#E7DED2] p-6 sm:p-8">
            @include('tutorial._form', [
                'aksi' => route('tutorial.update', $tutorial),
                'batal' => route('tutorial.show', $tutorial),
            ])
        </div>
    </div>
</x-app-layout>
