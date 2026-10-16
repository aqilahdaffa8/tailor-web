<x-app-layout title="Edit Pelanggan">
    <x-slot name="header">
        Edit Data Pelanggan
    </x-slot>

    <div class="max-w-xl">
        <div class="bg-white rounded-2xl border border-zinc-200 p-6 sm:p-8 shadow-2xs">
            <form action="{{ route('pelanggan.update', $pelanggan) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                {{-- Nama --}}
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1.5">
                        Nama Lengkap <span class="text-rose-600">*</span>
                    </label>
                    <input type="text" name="nama" value="{{ old('nama', $pelanggan->nama) }}" required autofocus
                           class="w-full px-3.5 py-2.5 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors @error('nama') border-rose-300 focus:border-rose-500 focus:ring-rose-500 @enderror">
                    @error('nama')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- No. HP --}}
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1.5">
                        No. WhatsApp / Telepon <span class="text-rose-600">*</span>
                    </label>
                    <input type="text" name="no_hp" value="{{ old('no_hp', $pelanggan->no_hp) }}" required
                           class="w-full px-3.5 py-2.5 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors @error('no_hp') border-rose-300 focus:border-rose-500 focus:ring-rose-500 @enderror">
                    @error('no_hp')
                        <p class="mt-1 text-xs text-rose-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Alamat --}}
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1.5">
                        Alamat
                    </label>
                    <textarea name="alamat" rows="3"
                              class="w-full px-3.5 py-2.5 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors resize-none">{{ old('alamat', $pelanggan->alamat) }}</textarea>
                </div>

                {{-- Catatan --}}
                <div>
                    <label class="block text-sm font-medium text-zinc-700 mb-1.5">
                        Catatan Internal
                    </label>
                    <textarea name="catatan" rows="2"
                              class="w-full px-3.5 py-2.5 bg-white border border-zinc-200 rounded-lg text-sm text-zinc-900 placeholder-zinc-400 focus:border-zinc-900 focus:ring-1 focus:ring-zinc-900 focus:outline-none transition-colors resize-none">{{ old('catatan', $pelanggan->catatan) }}</textarea>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="px-5 py-2.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-medium transition-colors shadow-2xs cursor-pointer">
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('pelanggan.show', $pelanggan) }}"
                       class="px-4 py-2.5 rounded-lg border border-zinc-200 text-sm font-medium text-zinc-700 hover:bg-zinc-50 transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
