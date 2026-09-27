<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Stored Procedure: Buat Pesanan Baru Atomik (Header + Item + Copy Template Tahapan ke Progress)
        // Dilengkapi Transaction, COMMIT, dan ROLLBACK
        DB::unprepared("
            DROP PROCEDURE IF EXISTS sp_buat_pesanan;
            CREATE PROCEDURE sp_buat_pesanan(
                IN p_nomor_pesanan VARCHAR(50),
                IN p_pelanggan_id BIGINT,
                IN p_tanggal_pesan DATE,
                IN p_deadline DATE,
                IN p_catatan TEXT,
                IN p_jenis_jasa_id BIGINT,
                IN p_harga DECIMAL(12,2),
                IN p_pola_detail_id BIGINT,
                IN p_pengukuran_id BIGINT,
                IN p_detail_pekerjaan TEXT,
                OUT p_pesanan_id BIGINT,
                OUT p_item_pesanan_id BIGINT,
                OUT p_status_code INT,
                OUT p_status_message VARCHAR(255)
            )
            BEGIN
                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    SET p_pesanan_id = NULL;
                    SET p_item_pesanan_id = NULL;
                    SET p_status_code = 500;
                    SET p_status_message = 'Terjadi kesalahan SQL, seluruh proses pembuatan pesanan di-ROLLBACK.';
                END;

                START TRANSACTION;

                -- Validasi keberadaan pelanggan & jenis jasa
                IF NOT EXISTS (SELECT 1 FROM pelanggan WHERE id = p_pelanggan_id) THEN
                    ROLLBACK;
                    SET p_pesanan_id = NULL;
                    SET p_item_pesanan_id = NULL;
                    SET p_status_code = 404;
                    SET p_status_message = 'Pelanggan tidak ditemukan.';
                ELSEIF NOT EXISTS (SELECT 1 FROM jenis_jasa WHERE id = p_jenis_jasa_id) THEN
                    ROLLBACK;
                    SET p_pesanan_id = NULL;
                    SET p_item_pesanan_id = NULL;
                    SET p_status_code = 404;
                    SET p_status_message = 'Jenis jasa tidak ditemukan.';
                ELSE
                    -- 1. Insert header pesanan
                    INSERT INTO pesanan (nomor_pesanan, pelanggan_id, tanggal_pesan, deadline, catatan, status, created_at, updated_at)
                    VALUES (p_nomor_pesanan, p_pelanggan_id, p_tanggal_pesan, p_deadline, p_catatan, 'aktif', NOW(), NOW());
                    
                    SET p_pesanan_id = LAST_INSERT_ID();

                    -- 2. Insert item pesanan
                    INSERT INTO item_pesanan (pesanan_id, jenis_jasa_id, pola_detail_id, pengukuran_id, detail_pekerjaan, harga, deadline, status_item, created_at, updated_at)
                    VALUES (p_pesanan_id, p_jenis_jasa_id, p_pola_detail_id, p_pengukuran_id, p_detail_pekerjaan, p_harga, p_deadline, 'menunggu', NOW(), NOW());
                    
                    SET p_item_pesanan_id = LAST_INSERT_ID();

                    -- 3. Salin template tahapan dari jenis jasa ke progress_item pesanan ini
                    INSERT INTO progress_item (item_pesanan_id, nama_tahapan, urutan, is_completed, created_at, updated_at)
                    SELECT p_item_pesanan_id, nama_tahapan, urutan, FALSE, NOW(), NOW()
                    FROM template_tahapan
                    WHERE jenis_jasa_id = p_jenis_jasa_id
                    ORDER BY urutan ASC;

                    -- 4. COMMIT jika seluruh langkah berhasil
                    COMMIT;
                    SET p_status_code = 200;
                    SET p_status_message = 'Pesanan, item, dan progress tahapan berhasil dibuat.';
                END IF;
            END;
        ");

        // 2. Stored Procedure: Catat Pemakaian Bahan dengan Validasi Stok & Transaction
        DB::unprepared("
            DROP PROCEDURE IF EXISTS sp_catat_pemakaian_bahan;
            CREATE PROCEDURE sp_catat_pemakaian_bahan(
                IN p_item_pesanan_id BIGINT,
                IN p_bahan_id BIGINT,
                IN p_jumlah DECIMAL(10,2),
                IN p_catatan TEXT,
                OUT p_status_code INT,
                OUT p_status_message VARCHAR(255)
            )
            BEGIN
                DECLARE v_stok_sekarang DECIMAL(10,2);

                DECLARE EXIT HANDLER FOR SQLEXCEPTION
                BEGIN
                    ROLLBACK;
                    SET p_status_code = 500;
                    SET p_status_message = 'Terjadi kesalahan sistem, transaksi pemakaian bahan di-ROLLBACK.';
                END;

                START TRANSACTION;

                -- Kunci baris bahan untuk mencegah race condition (Concurrency Lock)
                SELECT stok INTO v_stok_sekarang FROM bahan WHERE id = p_bahan_id FOR UPDATE;

                IF v_stok_sekarang IS NULL THEN
                    ROLLBACK;
                    SET p_status_code = 404;
                    SET p_status_message = 'Bahan tidak ditemukan.';
                ELSEIF v_stok_sekarang < p_jumlah THEN
                    ROLLBACK;
                    SET p_status_code = 400;
                    SET p_status_message = CONCAT('Stok tidak mencukupi! Sisa stok: ', v_stok_sekarang, ', diminta: ', p_jumlah);
                ELSE
                    -- Insert ke pemakaian_bahan (akan otomatis memicu Trigger tr_after_pemakaian_bahan_insert)
                    INSERT INTO pemakaian_bahan (item_pesanan_id, bahan_id, jumlah, catatan, created_at, updated_at)
                    VALUES (p_item_pesanan_id, p_bahan_id, p_jumlah, p_catatan, NOW(), NOW());

                    COMMIT;
                    SET p_status_code = 200;
                    SET p_status_message = 'Pemakaian bahan berhasil dicatat dan stok dipotong.';
                END IF;
            END;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_catat_pemakaian_bahan;');
        DB::unprepared('DROP PROCEDURE IF EXISTS sp_buat_pesanan;');
    }
};
