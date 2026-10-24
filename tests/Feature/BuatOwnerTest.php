<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class BuatOwnerTest extends TestCase
{
    use DatabaseTransactions;

    /**
     * Berhasil membuat owner baru secara interaktif.
     */
    public function test_berhasil_membuat_owner_secara_interaktif(): void
    {
        $this->artisan('user:buat-owner')
            ->expectsQuestion('Nama lengkap pemilik', 'Pemilik Baru')
            ->expectsQuestion('Alamat email pemilik', 'pemilikbaru@tailor.test')
            ->expectsQuestion('Password pemilik (minimal 12 karakter)', 'password-aman-123')
            ->expectsOutputToContain("Akun pemilik 'Pemilik Baru' (pemilikbaru@tailor.test) berhasil dibuat.")
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'name' => 'Pemilik Baru',
            'email' => 'pemilikbaru@tailor.test',
        ]);
    }

    /**
     * Berhasil membuat owner melalui opsi command.
     */
    public function test_berhasil_membuat_owner_melalui_opsi(): void
    {
        $this->artisan('user:buat-owner', [
            '--nama' => 'Owner Opsi',
            '--email' => 'owneropsi@tailor.test',
            '--password' => 'rahasia12345678',
        ])
            ->expectsOutputToContain("Akun pemilik 'Owner Opsi' (owneropsi@tailor.test) berhasil dibuat.")
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'email' => 'owneropsi@tailor.test',
        ]);
    }

    /**
     * Gagal jika password kurang dari 12 karakter.
     */
    public function test_gagal_jika_password_kurang_dari_12_karakter(): void
    {
        $this->artisan('user:buat-owner', [
            '--nama' => 'Owner Pendek',
            '--email' => 'pendek@tailor.test',
            '--password' => 'kurang12',
        ])
            ->expectsOutputToContain('Password minimal harus 12 karakter.')
            ->assertExitCode(1);

        $this->assertDatabaseMissing('users', [
            'email' => 'pendek@tailor.test',
        ]);
    }

    /**
     * Gagal jika email sudah terdaftar.
     */
    public function test_gagal_jika_email_sudah_terdaftar(): void
    {
        User::factory()->create(['email' => 'terdaftar@tailor.test']);

        $this->artisan('user:buat-owner', [
            '--nama' => 'Owner Duplikat',
            '--email' => 'terdaftar@tailor.test',
            '--password' => 'password-aman-123',
        ])
            ->expectsOutputToContain('Email sudah terdaftar.')
            ->assertExitCode(1);
    }
}
