@php
    $t = $tutorial ?? null;
    $tipeAwal = old('tipe', $t?->tipe ?? 'artikel');
    $inputClass = 'w-full px-3 py-2.5 bg-[#F7F4EE]/40 border border-[#E7DED2] rounded-xl text-sm focus:border-[#A8754F] focus:outline-none';
@endphp

<form action="{{ $aksi }}" method="POST" enctype="multipart/form-data" class="space-y-5"
      x-data="{ tipe: '{{ $tipeAwal }}' }">
    @csrf
    @if($t)
        @method('PUT')
    @endif

    {{-- Judul --}}
    <div>
        <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
            Judul <span class="text-[#B86B67]">*</span>
        </label>
        <input type="text" name="judul" value="{{ old('judul', $t?->judul) }}" required maxlength="255" class="{{ $inputClass }}">
        @error('judul') <p class="mt-1 text-xs text-[#B86B67]">{{ $message }}</p> @enderror
    </div>

    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        {{-- Kategori --}}
        <div>
            <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
                Kategori <span class="text-[#B86B67]">*</span>
            </label>
            <input type="text" name="kategori" value="{{ old('kategori', $t?->kategori) }}" required maxlength="50"
                   list="daftar-kategori" placeholder="Contoh: Pengukuran" class="{{ $inputClass }}">
            <datalist id="daftar-kategori">
                @foreach($kategoriList as $kat)
                    <option value="{{ $kat }}"></option>
                @endforeach
            </datalist>
            @error('kategori') <p class="mt-1 text-xs text-[#B86B67]">{{ $message }}</p> @enderror
        </div>

        {{-- Tipe --}}
        <div>
            <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
                Tipe <span class="text-[#B86B67]">*</span>
            </label>
            <select name="tipe" x-model="tipe" class="{{ $inputClass }}">
                <option value="artikel">Artikel</option>
                <option value="video">Video (YouTube)</option>
                <option value="pdf">File PDF</option>
            </select>
            @error('tipe') <p class="mt-1 text-xs text-[#B86B67]">{{ $message }}</p> @enderror
        </div>
    </div>

    {{-- Video --}}
    <div x-show="tipe === 'video'" x-cloak>
        <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
            Tautan YouTube <span class="text-[#B86B67]">*</span>
        </label>
        <input type="url" name="video_url" value="{{ old('video_url', $t?->tipe === 'video' ? $t->file_or_url : '') }}"
               placeholder="https://www.youtube.com/watch?v=..." class="{{ $inputClass }}">
        @error('video_url') <p class="mt-1 text-xs text-[#B86B67]">{{ $message }}</p> @enderror
    </div>

    {{-- PDF --}}
    <div x-show="tipe === 'pdf'" x-cloak>
        <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
            File PDF <span class="text-[#B86B67]">*</span>
        </label>
        <input type="file" name="file" accept="application/pdf" class="{{ $inputClass }}">
        <p class="text-[11px] text-[#9A816B] mt-1">Maksimal 10 MB.</p>
        @if($t?->tipe === 'pdf' && $t->file_url)
            <p class="text-[11px] text-[#9A816B] mt-1">
                File saat ini tersimpan. Pilih file baru hanya bila ingin menggantinya.
                <a href="{{ route('tutorial.unduh', $t) }}" class="text-[#A8754F] hover:underline">Unduh</a>
            </p>
        @endif
        @error('file') <p class="mt-1 text-xs text-[#B86B67]">{{ $message }}</p> @enderror
    </div>

    {{-- Gambar sampul (artikel) --}}
    <div x-show="tipe === 'artikel'" x-cloak>
        <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">Gambar Sampul (Opsional)</label>
        <input type="file" name="gambar" accept="image/png,image/jpeg,image/webp" class="{{ $inputClass }}">
        <p class="text-[11px] text-[#9A816B] mt-1">JPG, PNG, atau WEBP. Maksimal 5 MB.</p>
        @if($t?->tipe === 'artikel' && $t->file_url)
            <div class="mt-2 flex items-center gap-3">
                <img src="{{ $t->file_url }}" alt="Gambar sampul" class="h-16 w-16 rounded-xl object-cover border border-[#E7DED2]">
                <label class="flex items-center gap-2 text-xs text-[#9A816B]">
                    <input type="checkbox" name="hapus_gambar" value="1"> Hapus gambar saat ini
                </label>
            </div>
        @endif
        @error('gambar') <p class="mt-1 text-xs text-[#B86B67]">{{ $message }}</p> @enderror
    </div>

    {{-- Isi / deskripsi --}}
    <div>
        <label class="block text-xs font-semibold text-[#38443D] uppercase tracking-wide mb-1">
            <span x-text="tipe === 'artikel' ? 'Isi Artikel' : 'Deskripsi (Opsional)'"></span>
            <span class="text-[#B86B67]" x-show="tipe === 'artikel'">*</span>
        </label>
        <textarea name="konten" rows="12" maxlength="50000" class="{{ $inputClass }} resize-y">{{ old('konten', $t?->konten) }}</textarea>
        <p class="text-[11px] text-[#9A816B] mt-1">Teks biasa. Baris baru akan dipertahankan saat ditampilkan.</p>
        @error('konten') <p class="mt-1 text-xs text-[#B86B67]">{{ $message }}</p> @enderror
    </div>

    <div class="flex items-center gap-3 pt-2">
        <button type="submit"
                class="px-6 py-2.5 rounded-xl bg-[#29302D] hover:bg-[#38443D] text-white text-xs font-semibold transition-all cursor-pointer">
            Simpan Tutorial
        </button>
        <a href="{{ $batal }}"
           class="px-4 py-2.5 rounded-xl border border-[#E7DED2] text-xs font-semibold text-[#9A816B] hover:text-[#29302D] hover:bg-[#F7F4EE] transition-colors">
            Batal
        </a>
    </div>
</form>
