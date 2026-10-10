<x-app-layout title="Dashboard">
    <x-slot name="header">
        Ruang Kerja Atelier
    </x-slot>

    <!-- Welcome Hero Card -->
    <div class="relative bg-white rounded-3xl p-8 border border-[#E7DED2] overflow-hidden mb-8">
        <div class="absolute top-0 left-0 right-0 h-1.5 bg-[#29302D]"></div>
        <div class="stitching-line mb-6"></div>

        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
            <div>
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-semibold bg-[#A8754F]/15 text-[#A8754F] border border-[#A8754F]/30 mb-3">
                    <span class="w-1.5 h-1.5 rounded-full bg-[#A8754F]"></span>
                    Atelier Aktif
                </span>
                <h2 class="font-heading text-2xl sm:text-3xl font-bold text-[#29302D]">
                    Selamat Datang di Atelier, {{ auth()->user()->name }}!
                </h2>
                <p class="mt-2 text-sm text-[#9A816B] max-w-xl leading-relaxed">
                    Pantau pesanan, pembayaran, dan stok bahan Anda dalam satu tampilan. Data diperbarui otomatis setiap satu menit.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('pelanggan.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl border border-[#E7DED2] bg-white hover:bg-[#F7F4EE] text-[#38443D] text-sm font-semibold transition-all duration-200">
                    Tambah Pelanggan
                </a>
                <a href="{{ route('pesanan.create') }}"
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-[#29302D] hover:bg-[#38443D] text-white text-sm font-semibold transition-all duration-200">
                    <svg class="w-4 h-4 text-[#C49A52]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Buat Pesanan Baru</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Widget dengan auto-refresh (polling 60 detik) -->
    <div x-data="{
            memuat: false,
            diperbarui: '{{ now()->format('H:i') }}',
            async segarkan() {
                if (this.memuat || document.hidden) { return; }
                this.memuat = true;
                try {
                    const respon = await fetch('{{ route('dashboard') }}', {
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'text/html' },
                    });
                    if (respon.redirected) { window.location.reload(); return; }
                    if (respon.ok) {
                        this.$refs.konten.innerHTML = await respon.text();
                        this.diperbarui = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                    }
                } catch (galat) {
                    /* abaikan; coba lagi pada putaran berikutnya */
                } finally {
                    this.memuat = false;
                }
            }
         }"
         x-init="setInterval(() => segarkan(), 60000)">

        <div class="flex items-center justify-end gap-3 mb-5">
            <span class="text-xs text-[#9A816B]">Diperbarui pukul <span x-text="diperbarui"></span></span>
            <button type="button" @click="segarkan()" :disabled="memuat"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-[#E7DED2] bg-white text-xs font-semibold text-[#38443D] hover:bg-[#F7F4EE] transition-colors cursor-pointer disabled:opacity-50">
                <svg class="w-3.5 h-3.5" :class="memuat ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Segarkan
            </button>
        </div>

        <div x-ref="konten">
            @include('dashboard._widget')
        </div>
    </div>
</x-app-layout>
