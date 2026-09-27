<x-app-layout title="Dashboard">
    <x-slot name="header">
        Ruang Kerja Atelier
    </x-slot>

    <!-- Welcome Hero Card (Clean Atelier Border, No Harsh Shadow) -->
    <div class="relative bg-white rounded-3xl p-8 border border-[#E7DED2] overflow-hidden mb-8">
        <!-- Top Stitching Line -->
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
                    Sistem manajemen usaha jahit siap digunakan. Autentikasi web dan endpoint API untuk aplikasi mobile Kotlin telah aktif.
                </p>
            </div>

            <!-- Quick Action Button (Single Plus Icon) -->
            <div class="flex items-center gap-3">
                <a href="#" 
                   class="inline-flex items-center gap-2 px-5 py-3 rounded-2xl bg-[#29302D] hover:bg-[#38443D] text-white text-sm font-semibold transition-all duration-200 cursor-pointer">
                    <svg class="w-4 h-4 text-[#C49A52]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Buat Pesanan Baru</span>
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Status Grid Preview -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5">
        <!-- Card 1: Pesanan Aktif -->
        <div class="bg-white rounded-2xl p-5 border border-[#E7DED2] transition-colors hover:border-[#A8754F]/40">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-[#9A816B]">Pesanan Aktif</span>
                <span class="p-2 rounded-xl bg-[#F7F4EE] text-[#A8754F]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </span>
            </div>
            <p class="mt-4 text-3xl font-bold font-heading text-[#29302D]">0</p>
            <p class="mt-1 text-xs text-[#9A816B]">Sedang dalam pengerjaan</p>
        </div>

        <!-- Card 2: Mendekati Deadline -->
        <div class="bg-white rounded-2xl p-5 border border-[#E7DED2] transition-colors hover:border-[#A8754F]/40">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-[#9A816B]">Mendekati Deadline</span>
                <span class="p-2 rounded-xl bg-[#C49A52]/15 text-[#C49A52]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-4 text-3xl font-bold font-heading text-[#29302D]">0</p>
            <p class="mt-1 text-xs text-[#C49A52] font-medium">Dalam 3 hari ke depan</p>
        </div>

        <!-- Card 3: Belum Lunas -->
        <div class="bg-white rounded-2xl p-5 border border-[#E7DED2] transition-colors hover:border-[#A8754F]/40">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-[#9A816B]">Belum Lunas</span>
                <span class="p-2 rounded-xl bg-[#B86B67]/15 text-[#B86B67]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <p class="mt-4 text-3xl font-bold font-heading text-[#29302D]">Rp 0</p>
            <p class="mt-1 text-xs text-[#B86B67]">Sisa tagihan jasa</p>
        </div>

        <!-- Card 4: Bahan Menipis -->
        <div class="bg-white rounded-2xl p-5 border border-[#E7DED2] transition-colors hover:border-[#A8754F]/40">
            <div class="flex items-center justify-between">
                <span class="text-xs font-semibold uppercase tracking-wider text-[#9A816B]">Stok Menipis</span>
                <span class="p-2 rounded-xl bg-[#6F8A78]/15 text-[#6F8A78]">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </span>
            </div>
            <p class="mt-4 text-3xl font-bold font-heading text-[#29302D]">0</p>
            <p class="mt-1 text-xs text-[#6F8A78]">Perlu restock bahan</p>
        </div>
    </div>
</x-app-layout>
