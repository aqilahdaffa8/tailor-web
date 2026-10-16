<x-guest-layout title="Masuk">
    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10">
        <!-- Logo & Header Brand -->
        <div class="text-center">
            <h1 class="text-2xl font-bold tracking-tight text-zinc-900">
                Tailor Management System
            </h1>
            <p class="mt-2 text-sm text-zinc-500">
                Masuk ke akun Anda untuk mengelola usaha jahit
            </p>
        </div>

        <!-- Main Card -->
        <div class="mt-8 bg-white py-8 px-6 border border-zinc-200 rounded-2xl sm:px-10 relative overflow-hidden shadow-2xs">
            <!-- Flash Success Message -->
            @if (session('success'))
                <div class="mb-5 rounded-lg bg-emerald-50 p-4 border border-emerald-200 text-sm text-emerald-800 flex items-center gap-3">
                    <svg class="w-5 h-5 text-emerald-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Validation Error Summary -->
            @if ($errors->any())
                <div class="mb-5 rounded-lg bg-rose-50 p-4 border border-rose-200 text-sm text-rose-800 flex items-start gap-3">
                    <svg class="w-5 h-5 text-rose-600 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <p class="font-medium">Gagal Masuk:</p>
                        <p class="mt-0.5 text-xs text-rose-700">{{ $errors->first() }}</p>
                    </div>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-4">
                @csrf

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-sm font-medium text-zinc-700">
                        Alamat Email
                    </label>
                    <div class="mt-1.5 relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207" />
                            </svg>
                        </div>
                        <input id="email" 
                               name="email" 
                               type="email" 
                               autocomplete="email" 
                               required 
                               value="{{ old('email') }}"
                               placeholder="nama@email.com"
                               class="block w-full pl-10 pr-3 py-2 bg-white border @error('email') border-rose-300 focus:border-rose-500 focus:ring-rose-500 @else border-zinc-200 focus:border-zinc-900 focus:ring-zinc-900 @enderror rounded-lg text-sm text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-1 transition-colors">
                    </div>
                </div>

                <!-- Password Input with Toggle Visibility -->
                <div x-data="{ show: false }">
                    <label for="password" class="block text-sm font-medium text-zinc-700">
                        Kata Sandi
                    </label>
                    <div class="mt-1.5 relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-zinc-400">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z" />
                            </svg>
                        </div>
                        <input id="password" 
                               name="password" 
                               :type="show ? 'text' : 'password'" 
                               type="password"
                               autocomplete="current-password" 
                               required 
                               placeholder="••••••••"
                               class="block w-full pl-10 pr-11 py-2 bg-white border @error('password') border-rose-300 focus:border-rose-500 focus:ring-rose-500 @else border-zinc-200 focus:border-zinc-900 focus:ring-zinc-900 @enderror rounded-lg text-sm text-zinc-900 placeholder-zinc-400 focus:outline-none focus:ring-1 transition-colors">
                        
                        <!-- Toggle Button (Icon Mata Bersih & Berfungsi) -->
                        <button type="button" 
                                @click="show = !show" 
                                onclick="const input = document.getElementById('password'); if(input.type === 'password'){ input.type = 'text'; } else { input.type = 'password'; }"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center text-zinc-400 hover:text-zinc-700 transition-colors focus:outline-none cursor-pointer"
                                aria-label="Tampilkan atau sembunyikan kata sandi">
                            <svg x-show="!show" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <svg x-show="show" x-cloak class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input id="remember" 
                               name="remember" 
                               type="checkbox" 
                               class="w-4 h-4 rounded border-zinc-300 text-zinc-900 focus:ring-zinc-900/20 focus:ring-offset-0 transition-colors">
                        <span class="text-sm text-zinc-600 select-none">Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" 
                            class="w-full flex items-center justify-center gap-2 py-2.5 px-4 rounded-lg text-sm font-medium text-white bg-zinc-900 hover:bg-zinc-800 active:scale-[0.99] focus:outline-none focus:ring-2 focus:ring-zinc-900 focus:ring-offset-2 transition-all cursor-pointer shadow-2xs">
                        <span>Masuk</span>
                        <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </div>
            </form>

            <!-- Demo Account Helper Box -->
            <div class="mt-6 pt-5 border-t border-zinc-100 text-center">
                <p class="text-xs text-zinc-500 mb-2 font-medium">Akun Pemilik (Seeded Default):</p>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-zinc-100 border border-zinc-200 text-xs text-zinc-700 font-mono">
                    <span>owner@tailor.test</span>
                    <span class="text-zinc-400">/</span>
                    <span>password</span>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
