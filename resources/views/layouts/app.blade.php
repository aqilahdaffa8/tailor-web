<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Tailor Management System') }} - {{ $title ?? 'Dashboard' }}</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-[#F7F4EE] text-[#29302D] font-sans antialiased selection:bg-[#9A816B] selection:text-white" x-data="{ sidebarOpen: false }">
    <div class="min-h-full flex">
        <!-- Sidebar Navigation (Desktop) -->
        <aside class="hidden lg:flex lg:flex-col lg:w-64 bg-[#29302D] text-[#F7F4EE] border-r border-[#38443D] fixed inset-y-0 z-30 transition-all duration-300">
            <!-- Brand Atelier -->
            <div class="flex items-center gap-3 px-6 h-18 border-b border-[#38443D]/60 bg-[#29302D]">
                <div class="w-10 h-10 rounded-xl bg-[#38443D] flex items-center justify-center border border-[#A8754F]/40 shadow-xs">
                    <svg class="w-5 h-5 text-[#C49A52]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <circle cx="6" cy="6" r="3" stroke-width="2"/>
                        <circle cx="6" cy="18" r="3" stroke-width="2"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 4L8.12 15.88M14.47 14.48L20 20M8.12 8.12L12 12"/>
                    </svg>
                </div>
                <div>
                    <h2 class="font-heading font-bold text-sm tracking-wide text-white">TAILOR SYSTEM</h2>
                    <p class="text-[10px] text-[#9A816B] uppercase tracking-wider">Digital Atelier</p>
                </div>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-4 py-5 space-y-1.5 overflow-y-auto">
                <!-- Dashboard -->
                <a href="{{ route('dashboard') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-[#38443D] text-[#C49A52] shadow-xs' : 'text-[#E7DED2]/80 hover:text-white hover:bg-[#38443D]/50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Dashboard</span>
                </a>

                <!-- Pelanggan -->
                <a href="{{ route('pelanggan.index') }}"
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('pelanggan.*') ? 'bg-[#38443D] text-[#C49A52] shadow-xs' : 'text-[#E7DED2]/80 hover:text-white hover:bg-[#38443D]/50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>Pelanggan</span>
                </a>

                <!-- Pesanan Jahit -->
                <a href="#" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-[#E7DED2]/80 hover:text-white hover:bg-[#38443D]/50 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <span>Pesanan Jahit</span>
                </a>

                <!-- Library Pola -->
                <a href="{{ route('pola.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('pola.*') ? 'bg-[#38443D] text-[#C49A52] shadow-xs' : 'text-[#E7DED2]/80 hover:text-white hover:bg-[#38443D]/50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Library Pola</span>
                </a>

                <!-- Pengukuran -->
                <a href="#" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-[#E7DED2]/80 hover:text-white hover:bg-[#38443D]/50 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <span>Pengukuran</span>
                </a>

                <!-- Inventory -->
                <a href="#" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-[#E7DED2]/80 hover:text-white hover:bg-[#38443D]/50 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span>Inventory Bahan</span>
                </a>

                <!-- Keuangan -->
                <a href="#" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium text-[#E7DED2]/80 hover:text-white hover:bg-[#38443D]/50 transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Keuangan</span>
                </a>

                <!-- Pengaturan -->
                <a href="{{ route('pengaturan.index') }}" 
                   class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('pengaturan.*') ? 'bg-[#38443D] text-[#C49A52] shadow-xs' : 'text-[#E7DED2]/80 hover:text-white hover:bg-[#38443D]/50' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Pengaturan</span>
                </a>
            </nav>

            <!-- User Footer in Sidebar -->
            <div class="p-4 border-t border-[#38443D]/60 bg-[#29302D]">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-[#A8754F] flex items-center justify-center font-bold text-white text-xs">
                            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="overflow-hidden">
                            <p class="text-xs font-semibold text-white truncate">{{ auth()->user()->name ?? 'Owner' }}</p>
                            <p class="text-[10px] text-[#9A816B] truncate">{{ auth()->user()->email ?? '' }}</p>
                        </div>
                    </div>

                    <!-- Logout Form -->
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" 
                                title="Keluar" 
                                class="p-1.5 rounded-lg text-[#9A816B] hover:text-white hover:bg-[#38443D] transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content Wrapper -->
        <div class="flex-1 lg:pl-64 flex flex-col min-w-0">
            <!-- Topbar Header -->
            <header class="h-18 bg-white border-b border-[#E7DED2] flex items-center justify-between px-6 sticky top-0 z-20 shadow-2xs">
                <div class="flex items-center gap-4">
                    <!-- Mobile Hamburger -->
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-xl text-[#29302D] hover:bg-[#F7F4EE]">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <h1 class="font-heading font-semibold text-lg text-[#29302D]">
                        {{ $header ?? 'Dashboard' }}
                    </h1>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Notification Reminder Icon -->
                    <button class="relative p-2 rounded-xl text-[#38443D] hover:bg-[#F7F4EE] transition-colors cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                        <span class="absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-[#A8754F]"></span>
                    </button>

                    <div class="h-6 w-px bg-[#E7DED2]"></div>

                    <span class="text-xs font-medium text-[#38443D] hidden sm:inline-block">
                        {{ auth()->user()->name ?? 'Pemilik' }}
                    </span>
                </div>
            </header>

            <!-- Main Content Body -->
            <main class="flex-1 p-6 sm:p-8 max-w-7xl w-full mx-auto">
                <!-- Flash Messages -->
                @if (session('success'))
                    <div class="mb-6 rounded-2xl bg-[#6F8A78]/15 p-4 border border-[#6F8A78]/40 text-sm text-[#38443D] flex items-center justify-between shadow-2xs">
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-[#6F8A78] text-white flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <span class="font-medium">{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>

</body>
</html>
