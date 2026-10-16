<x-app-layout title="Dashboard">
    <x-slot name="header">
        Dashboard
    </x-slot>

    <!-- Welcome Hero Card -->
    <div class="relative bg-white rounded-2xl p-6 sm:p-8 border border-zinc-200 overflow-hidden mb-8 shadow-2xs">
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 relative z-10">
            <div>
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-xs font-medium bg-zinc-100 text-zinc-700 border border-zinc-200 mb-3">
                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                    Sistem Aktif
                </span>
                <h2 class="text-2xl sm:text-3xl font-bold tracking-tight text-zinc-900">
                    Selamat Datang, {{ auth()->user()->name }}!
                </h2>
                <p class="mt-2 text-sm text-zinc-500 max-w-xl leading-relaxed">
                    Pantau pesanan, pembayaran, dan stok bahan Anda dalam satu tampilan. Data diperbarui otomatis setiap satu menit.
                </p>
            </div>

            <div class="flex flex-wrap items-center gap-3">
                <a href="{{ route('pelanggan.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg border border-zinc-200 bg-white hover:bg-zinc-50 text-zinc-700 text-sm font-medium transition-colors">
                    Tambah Pelanggan
                </a>
                <a href="{{ route('pesanan.create') }}"
                   class="inline-flex items-center gap-2 px-4 py-2.5 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-sm font-medium transition-colors shadow-2xs">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <span class="text-xs text-zinc-500">Diperbarui pukul <span x-text="diperbarui"></span></span>
            <button type="button" @click="segarkan()" :disabled="memuat"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg border border-zinc-200 bg-white text-xs font-medium text-zinc-700 hover:bg-zinc-50 transition-colors cursor-pointer disabled:opacity-50">
                <svg class="w-3.5 h-3.5 text-zinc-500" :class="memuat ? 'animate-spin' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
