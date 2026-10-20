<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kategori_tutorial', function (Blueprint $table) {
            $table->id();
            $table->string('nama_kategori', 50)->unique();
            $table->string('deskripsi')->nullable();
            $table->timestamps();
        });

        Schema::table('tutorial', function (Blueprint $table) {
            $table->foreignId('kategori_tutorial_id')
                ->nullable()
                ->after('kategori')
                ->constrained('kategori_tutorial')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tutorial', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kategori_tutorial_id');
        });

        Schema::dropIfExists('kategori_tutorial');
    }
};
