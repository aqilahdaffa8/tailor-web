<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

class BuatOwner extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:buat-owner
                            {--nama= : Nama lengkap pemilik}
                            {--email= : Alamat email pemilik}
                            {--password= : Password akun (minimal 12 karakter)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Membuat akun pengguna pemilik (owner) sistem tailor';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $nama = $this->option('nama') ?? $this->ask('Nama lengkap pemilik');
        $email = $this->option('email') ?? $this->ask('Alamat email pemilik');
        $password = $this->option('password') ?? $this->secret('Password pemilik (minimal 12 karakter)');

        $validator = Validator::make([
            'nama' => $nama,
            'email' => $email,
            'password' => $password,
        ], [
            'nama' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12'],
        ], [
            'nama.required' => 'Nama pemilik wajib diisi.',
            'email.required' => 'Email pemilik wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal harus 12 karakter.',
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $user = User::create([
            'name' => $nama,
            'email' => $email,
            'password' => $password,
        ]);

        $this->info("Akun pemilik '{$user->name}' ({$user->email}) berhasil dibuat.");

        return self::SUCCESS;
    }
}
