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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-zinc-50 text-zinc-900 font-sans antialiased selection:bg-zinc-900 selection:text-white" x-data="{ sidebarOpen: false }">
    <div class="min-h-full flex">
        <!-- Mobile Sidebar Backdrop -->
        <div x-show="sidebarOpen" 
             x-cloak
             x-transition:enter="transition-opacity ease-linear duration-200"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-linear duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="sidebarOpen = false" 
             class="fixed inset-0 bg-zinc-950/60 backdrop-blur-xs z-40 lg:hidden">
        </div>

        <!-- Sidebar Navigation (True Neutral Charcoal Zinc) -->
        <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
               class="fixed inset-y-0 left-0 z-50 w-64 bg-zinc-900 border-r border-zinc-800 flex flex-col -translate-x-full lg:translate-x-0 transition-transform duration-200 ease-in-out">
            <!-- Brand Header -->
            <div class="flex items-center justify-between px-6 h-16 border-b border-zinc-800 bg-zinc-900 shrink-0">
                <a href="{{ route('dashboard') }}" class="font-bold text-base tracking-tight text-white hover:text-zinc-200 transition-colors">
                    TAILOR SYSTEM
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden p-1.5 rounded-lg text-zinc-400 hover:text-white hover:bg-zinc-800 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Navigation Links -->
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                <!-- 1. Dashboard -->
                <a href="{{ route('dashboard') }}" 
                   class="group flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('dashboard') ? 'bg-zinc-800 text-white font-semibold shadow-2xs' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/60' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('dashboard') ? 'text-white' : 'text-zinc-400 group-hover:text-zinc-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span>Dashboard</span>
                </a>

                <!-- 2. Pelanggan -->
                <a href="{{ route('pelanggan.index') }}"
                   class="group flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('pelanggan.*') ? 'bg-zinc-800 text-white font-semibold shadow-2xs' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/60' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('pelanggan.*') ? 'text-white' : 'text-zinc-400 group-hover:text-zinc-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span>Pelanggan</span>
                </a>

                <!-- 3. Pesanan Jahit -->
                <a href="{{ route('pesanan.index') }}"
                   class="group flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('pesanan.*') ? 'bg-zinc-800 text-white font-semibold shadow-2xs' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/60' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('pesanan.*') ? 'text-white' : 'text-zinc-400 group-hover:text-zinc-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <span>Pesanan Jahit</span>
                </a>

                <!-- 4. Library Pola -->
                <a href="{{ route('pola.index') }}" 
                   class="group flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('pola.*') ? 'bg-zinc-800 text-white font-semibold shadow-2xs' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/60' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('pola.*') ? 'text-white' : 'text-zinc-400 group-hover:text-zinc-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span>Library Pola</span>
                </a>

                <!-- 5. Pengukuran -->
                <a href="{{ route('pengukuran.index') }}" 
                   class="group flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('pengukuran.*') ? 'bg-zinc-800 text-white font-semibold shadow-2xs' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/60' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('pengukuran.*') ? 'text-white' : 'text-zinc-400 group-hover:text-zinc-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                    <span>Pengukuran</span>
                </a>

                <!-- 6. Inventory (Disembunyikan sesuai Opsi A) -->
                {{--
                <a href="{{ route('inventory.index') }}" 
                   class="group flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('inventory.*') ? 'bg-zinc-800 text-white font-semibold shadow-2xs' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/60' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('inventory.*') ? 'text-white' : 'text-zinc-400 group-hover:text-zinc-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span>Inventory Bahan</span>
                </a>
                --}}

                <!-- 7. Keuangan -->
                <a href="{{ route('keuangan.index') }}" 
                   class="group flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('keuangan.*') ? 'bg-zinc-800 text-white font-semibold shadow-2xs' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/60' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('keuangan.*') ? 'text-white' : 'text-zinc-400 group-hover:text-zinc-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span>Keuangan</span>
                </a>

                <!-- 8. Tutorial -->
                <a href="{{ route('tutorial.index') }}" 
                   class="group flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('tutorial.*') ? 'bg-zinc-800 text-white font-semibold shadow-2xs' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/60' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('tutorial.*') ? 'text-white' : 'text-zinc-400 group-hover:text-zinc-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span>Tutorial</span>
                </a>

                <!-- 9. Pengaturan -->
                <a href="{{ route('pengaturan.index') }}" 
                   class="group flex items-center gap-3 px-3.5 py-2.5 rounded-lg text-sm font-medium transition-colors {{ request()->routeIs('pengaturan.*') ? 'bg-zinc-800 text-white font-semibold shadow-2xs' : 'text-zinc-400 hover:text-white hover:bg-zinc-800/60' }}">
                    <svg class="w-5 h-5 {{ request()->routeIs('pengaturan.*') ? 'text-white' : 'text-zinc-400 group-hover:text-zinc-200' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span>Pengaturan</span>
                </a>
            </nav>

            <!-- User Profile & Logout Footer (True Neutral Charcoal Zinc) -->
            <div class="p-3.5 border-t border-zinc-800 bg-zinc-900 shrink-0">
                <div class="flex items-center justify-between gap-2">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-8 h-8 rounded-full bg-zinc-800 border border-zinc-700 text-zinc-200 flex items-center justify-center font-semibold text-xs shrink-0">
                            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                        </div>
                        <div class="overflow-hidden min-w-0">
                            <p class="text-sm font-medium text-white truncate">{{ auth()->user()->name ?? 'Pemilik' }}</p>
                            <p class="text-xs text-zinc-400 truncate">{{ auth()->user()->email ?? '' }}</p>
                        </div>
                    </div>

                    <!-- Logout Form -->
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" 
                                title="Keluar" 
                                class="p-2 rounded-lg text-zinc-400 hover:text-white hover:bg-zinc-800 transition-colors cursor-pointer">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
            <header class="h-16 bg-white border-b border-zinc-200 flex items-center justify-between px-4 sm:px-6 sticky top-0 z-20">
                <div class="flex items-center gap-4">
                    <!-- Mobile Hamburger -->
                    <button @click="sidebarOpen = !sidebarOpen" class="lg:hidden p-2 rounded-lg text-zinc-600 hover:text-zinc-900 hover:bg-zinc-100 cursor-pointer">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <h1 class="font-bold text-lg text-zinc-900 tracking-tight">
                        {{ $header ?? 'Dashboard' }}
                    </h1>
                </div>

                <div class="flex items-center gap-3">
                    <!-- Notification Reminder Icon -->
                    <div class="relative" x-data="{ open: false }" @click.away="open = false" @keydown.escape.window="open = false">
                        <button type="button" @click="open = !open" title="Pengingat"
                                class="relative p-2 rounded-lg text-zinc-500 hover:text-zinc-900 hover:bg-zinc-100 transition-colors cursor-pointer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            @if(($reminderJumlah ?? 0) > 0)
                                <span class="absolute top-1 right-1 min-w-4 h-4 px-1 rounded-full bg-rose-500 text-white text-[10px] font-semibold flex items-center justify-center">
                                    {{ $reminderJumlah > 99 ? '99+' : $reminderJumlah }}
                                </span>
                            @endif
                        </button>

                        <div x-show="open" x-cloak x-transition
                             class="absolute right-0 mt-2 w-80 sm:w-96 bg-white rounded-2xl border border-zinc-200 shadow-lg z-30 overflow-hidden">
                            <div class="flex items-center justify-between px-4 py-3 border-b border-zinc-100 bg-zinc-50/80">
                                <h3 class="font-semibold text-sm text-zinc-900">Pengingat</h3>
                                @if(($reminderJumlah ?? 0) > 0)
                                    <form action="{{ route('reminders.dismiss-semua') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-xs font-medium text-zinc-600 hover:text-zinc-900 cursor-pointer">Tandai semua dibaca</button>
                                    </form>
                                @endif
                            </div>

                            <div class="max-h-96 overflow-y-auto divide-y divide-zinc-100">
                                @forelse(($reminderDaftar ?? collect()) as $reminder)
                                    <div class="flex items-start gap-3 px-4 py-3 hover:bg-zinc-50/80 transition-colors">
                                        <a href="{{ $reminder->tautan ?? '#' }}" class="flex-1 min-w-0">
                                            <p class="text-xs font-semibold text-zinc-900">{{ $reminder->judul }}</p>
                                            <p class="text-xs text-zinc-500 mt-0.5">{{ $reminder->pesan }}</p>
                                        </a>
                                        <form action="{{ route('reminders.dismiss', $reminder) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <button type="submit" title="Tandai sudah dibaca"
                                                    class="p-1 rounded-lg text-zinc-400 hover:text-zinc-900 hover:bg-zinc-100 cursor-pointer">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                                </svg>
                                            </button>
                                        </form>
                                    </div>
                                @empty
                                    <p class="px-4 py-8 text-center text-xs text-zinc-500">Tidak ada pengingat baru.</p>
                                @endforelse
                            </div>

                            <form action="{{ route('reminders.sinkronkan') }}" method="POST" class="px-4 py-3 border-t border-zinc-100 bg-zinc-50/80">
                                @csrf
                                <button type="submit" class="text-xs font-medium text-zinc-700 hover:text-zinc-900 cursor-pointer">Periksa sekarang</button>
                            </form>
                        </div>
                    </div>

                    <div class="h-5 w-px bg-zinc-200"></div>

                    <span class="text-sm font-medium text-zinc-700 hidden sm:inline-block">
                        {{ auth()->user()->name ?? 'Pemilik' }}
                    </span>
                </div>
            </header>

            <!-- Main Content Body -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl w-full mx-auto">
                <!-- Flash Messages -->
                @if (session('success'))
                    <div class="mb-6 rounded-xl bg-emerald-50 p-4 border border-emerald-200 text-sm text-emerald-900 flex items-center justify-between shadow-2xs">
                        <div class="flex items-center gap-3">
                            <div class="w-7 h-7 rounded-full bg-emerald-600 text-white flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <span class="font-medium">{{ session('success') }}</span>
                        </div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="mb-6 rounded-xl bg-rose-50 p-4 border border-rose-200 text-sm text-rose-900 flex items-center gap-3 shadow-2xs">
                        <div class="w-7 h-7 rounded-full bg-rose-600 text-white flex items-center justify-center shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                        <span class="font-medium">{{ session('error') }}</span>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6 rounded-xl bg-rose-50 p-4 border border-rose-200 text-sm text-rose-900 shadow-2xs">
                        <p class="font-semibold text-rose-900 mb-1.5">Data belum dapat disimpan:</p>
                        <ul class="list-disc pl-5 space-y-0.5 text-xs text-rose-700">
                            @foreach ($errors->all() as $pesanError)
                                <li>{{ $pesanError }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{ $slot }}
            </main>
        </div>
    </div>
</body>
</html>
