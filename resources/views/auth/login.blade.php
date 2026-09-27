<x-guest-layout title="Masuk ke Atelier">
    <div class="sm:mx-auto sm:w-full sm:max-w-md relative z-10">
        <!-- Logo & Header Atelier -->
        <div class="text-center">
            <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-[#29302D] text-[#F7F4EE] border border-[#E7DED2] mb-4 relative group">
                <!-- Scissors Icon with Thread Detail -->
                <svg class="w-8 h-8 text-[#C49A52] transition-transform duration-300 group-hover:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <circle cx="6" cy="6" r="3" stroke-width="2"/>
                    <circle cx="6" cy="18" r="3" stroke-width="2"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 4L8.12 15.88M14.47 14.48L20 20M8.12 8.12L12 12"/>
                </svg>
                <!-- Decorative stitch corner dot -->
                <div class="absolute -top-1 -right-1 w-2.5 h-2.5 rounded-full bg-[#A8754F]"></div>
            </div>

            <h1 class="font-heading text-2xl font-bold tracking-tight text-[#29302D]">
                Tailor Management System
            </h1>
            <p class="mt-1 text-sm text-[#9A816B]">
                Ruang Kerja & Atelier Digital Penjahit
            </p>
        </div>

        <!-- Main Card (Clean Atelier Border, No Muddy Shadow) -->
        <div class="mt-8 bg-white py-8 px-6 border border-[#E7DED2] rounded-2xl sm:px-10 relative overflow-hidden">
            <!-- Top Stitching Line Accent -->
            <div class="absolute top-0 left-0 right-0 h-1 bg-[#29302D]"></div>
            <div class="stitching-line mb-6"></div>

            <!-- Flash Success Message -->
            @if (session('success'))
                <div class="mb-5 rounded-xl bg-[#6F8A78]/10 p-4 border border-[#6F8A78]/30 text-sm text-[#38443D] flex items-center gap-3">
                    <svg class="w-5 h-5 text-[#6F8A78] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Validation Error Summary -->
            @if ($errors->any())
                <div class="mb-5 rounded-xl bg-[#B86B67]/10 p-4 border border-[#B86B67]/30 text-sm text-[#B86B67] flex items-start gap-3">
                    <svg class="w-5 h-5 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <div>
                        <p class="font-medium">Gagal Masuk:</p>
                        <p class="mt-0.5 text-xs">{{ $errors->first() }}</p>
                    </div>
                </div>
            @endif

            <form action="{{ route('login.post') }}" method="POST" class="space-y-5">
                @csrf

                <!-- Email Input -->
                <div>
                    <label for="email" class="block text-xs font-semibold uppercase tracking-wider text-[#38443D]">
                        Alamat Email
                    </label>
                    <div class="mt-1 relative rounded-xl">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#9A816B]">
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
                               placeholder="nama@atelier.com"
                               class="block w-full pl-10 pr-3 py-2.5 bg-[#F7F4EE]/40 border @error('email') border-[#B86B67] @else border-[#E7DED2] @enderror rounded-xl text-sm text-[#29302D] placeholder-[#9A816B]/60 focus:outline-none focus:border-[#A8754F] focus:ring-1 focus:ring-[#A8754F] transition-all duration-200">
                    </div>
                </div>

                <!-- Password Input with Toggle Visibility -->
                <div x-data="{ show: false }">
                    <label for="password" class="block text-xs font-semibold uppercase tracking-wider text-[#38443D]">
                        Kata Sandi
                    </label>
                    <div class="mt-1 relative rounded-xl">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-[#9A816B]">
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
                               class="block w-full pl-10 pr-11 py-2.5 bg-[#F7F4EE]/40 border @error('password') border-[#B86B67] @else border-[#E7DED2] @enderror rounded-xl text-sm text-[#29302D] placeholder-[#9A816B]/60 focus:outline-none focus:border-[#A8754F] focus:ring-1 focus:ring-[#A8754F] transition-all duration-200">
                        
                        <!-- Toggle Button (Icon Mata Bersih & Berfungsi) -->
                        <button type="button" 
                                @click="show = !show" 
                                onclick="const input = document.getElementById('password'); if(input.type === 'password'){ input.type = 'text'; } else { input.type = 'password'; }"
                                class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-[#9A816B] hover:text-[#29302D] transition-colors focus:outline-none cursor-pointer"
                                aria-label="Tampilkan atau sembunyikan kata sandi">
                            <!-- Mata Terbuka (tampil saat password tersembunyi) -->
                            <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            </svg>
                            <!-- Mata Dicoret (tampil saat password terbuka) -->
                            <svg x-show="show" x-cloak class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 001.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.45 10.45 0 0112 4.5c4.756 0 8.773 3.162 10.065 7.498a10.523 10.523 0 01-4.293 5.774M6.228 6.228L3 3m3.228 3.228l3.65 3.65m7.894 7.894L21 21m-3.228-3.228l-3.65-3.65m0 0a3 3 0 10-4.243-4.243m4.242 4.242L9.88 9.88" />
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Remember Me Checkbox -->
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center gap-2.5 cursor-pointer">
                        <input id="remember" 
                               name="remember" 
                               type="checkbox" 
                               class="w-4 h-4 rounded border-[#E7DED2] text-[#A8754F] focus:ring-[#A8754F]/30 focus:ring-offset-0 transition-colors">
                        <span class="text-xs text-[#38443D] select-none">Ingat saya di perangkat ini</span>
                    </label>
                </div>

                <!-- Submit Button -->
                <div class="pt-2">
                    <button type="submit" 
                            class="w-full flex items-center justify-center gap-2 py-3 px-4 rounded-xl text-sm font-semibold text-white bg-[#29302D] hover:bg-[#38443D] active:scale-[0.98] focus:outline-none focus:ring-2 focus:ring-[#A8754F] focus:ring-offset-2 transition-all duration-200 cursor-pointer">
                        <span>Masuk ke Atelier</span>
                        <svg class="w-4 h-4 text-[#C49A52]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                        </svg>
                    </button>
                </div>
            </form>

            <!-- Demo Account Helper Box -->
            <div class="mt-6 pt-5 border-t border-dashed border-[#E7DED2] text-center">
                <p class="text-xs text-[#9A816B] mb-2 font-medium">Akun Pemilik (Seeded Default):</p>
                <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-lg bg-[#F7F4EE] border border-[#E7DED2] text-xs text-[#38443D] font-mono">
                    <span>owner@tailor.test</span>
                    <span class="text-[#9A816B]">/</span>
                    <span>password</span>
                </div>
            </div>
        </div>
    </div>
</x-guest-layout>
