<x-app-layout title="Edit Pelanggan">
    <x-slot name="header">
        Edit Data Pelanggan
    </x-slot>

    <div class="max-w-xl">
        <div class="bg-white rounded-2xl border border-[#E7DED2] p-6 sm:p-8">
            <form action="{{ route('pelanggan.update', $pelanggan) }}" method="POST" class="space-y-5">
                @csrf
                @method('PUT')

                {{-- Nama --}}
                <div>
                    <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
                        Nama Lengkap <span class="text-[#B86B67]">*</span>
                    </label>
                    <input type="text" name="nama" value="{{ old('nama', $pelanggan->nama) }}" required autofocus
                           class="w-full px-3 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none @error('nama') border-[#B86B67] @enderror">
                    @error('nama')
                        <p class="mt-1 text-xs text-[#B86B67]">{{ $message }}</p>
                    @enderror
                </div>

                {{-- No. HP --}}
                <div>
                    <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
                        No. WhatsApp / Telepon <span class="text-[#B86B67]">*</span>
                    </label>
                    <input type="text" name="no_hp" value="{{ old('no_hp', $pelanggan->no_hp) }}" required
                           class="w-full px-3 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none @error('no_hp') border-[#B86B67] @enderror">
                    @error('no_hp')
                        <p class="mt-1 text-xs text-[#B86B67]">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Alamat --}}
                <div>
                    <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
                        Alamat
                    </label>
                    <textarea name="alamat" rows="3"
                              class="w-full px-3 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none resize-none">{{ old('alamat', $pelanggan->alamat) }}</textarea>
                </div>

                {{-- Catatan --}}
                <div>
                    <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
                        Catatan Internal
                    </label>
                    <textarea name="catatan" rows="2"
                              class="w-full px-3 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none resize-none">{{ old('catatan', $pelanggan->catatan) }}</textarea>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit"
                            class="px-6 py-2.5 rounded-xl bg-[#29302D] hover:bg-[#38443D] text-white text-xs font-semibold transition-all cursor-pointer">
                        Simpan Perubahan
                    </button>
                    <a href="{{ route('pelanggan.show', $pelanggan) }}"
                       class="px-4 py-2.5 rounded-xl border border-[#E7DED2] text-xs font-semibold text-[#9A816B] hover:text-[#29302D] hover:bg-[#F7F4EE] transition-colors">
                        Batal
                    </a>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
