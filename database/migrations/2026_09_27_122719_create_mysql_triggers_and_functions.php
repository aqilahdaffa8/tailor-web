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
        // 1. Trigger saat pemakaian bahan dicatat -> potong stok otomatis & catat riwayat
        DB::unprepared("
            DROP TRIGGER IF EXISTS tr_after_pemakaian_bahan_insert;
            CREATE TRIGGER tr_after_pemakaian_bahan_insert
            AFTER INSERT ON pemakaian_bahan
            FOR EACH ROW
            BEGIN
                DECLARE v_stok_sebelum DECIMAL(10,2);
                SELECT stok INTO v_stok_sebelum FROM bahan WHERE id = NEW.bahan_id;

                UPDATE bahan 
                SET stok = stok - NEW.jumlah, updated_at = NOW() 
                WHERE id = NEW.bahan_id;

                INSERT INTO riwayat_stok (bahan_id, jenis, jumlah, stok_sebelum, stok_sesudah, keterangan, created_at, updated_at)
                VALUES (
                    NEW.bahan_id, 
                    'keluar', 
                    NEW.jumlah, 
                    v_stok_sebelum, 
                    v_stok_sebelum - NEW.jumlah, 
                    CONCAT('Pemakaian item pesanan #', NEW.item_pesanan_id), 
                    NOW(), 
                    NOW()
                );
            END;
        ");

        // 2. Trigger saat pemakaian bahan dihapus -> kembalikan stok otomatis & catat riwayat
        DB::unprepared("
            DROP TRIGGER IF EXISTS tr_after_pemakaian_bahan_delete;
            CREATE TRIGGER tr_after_pemakaian_bahan_delete
            AFTER DELETE ON pemakaian_bahan
            FOR EACH ROW
            BEGIN
                DECLARE v_stok_sebelum DECIMAL(10,2);
                SELECT stok INTO v_stok_sebelum FROM bahan WHERE id = OLD.bahan_id;

                UPDATE bahan 
                SET stok = stok + OLD.jumlah, updated_at = NOW() 
                WHERE id = OLD.bahan_id;

                INSERT INTO riwayat_stok (bahan_id, jenis, jumlah, stok_sebelum, stok_sesudah, keterangan, created_at, updated_at)
                VALUES (
                    OLD.bahan_id, 
                    'masuk', 
                    OLD.jumlah, 
                    v_stok_sebelum, 
                    v_stok_sebelum + OLD.jumlah, 
                    CONCAT('Pembatalan pemakaian item #', OLD.item_pesanan_id), 
                    NOW(), 
                    NOW()
                );
            END;
        ");

        // 3. Stored Function: Menghitung sisa pembayaran suatu pesanan
        DB::unprepared('
            DROP FUNCTION IF EXISTS fn_sisa_pembayaran;
            CREATE FUNCTION fn_sisa_pembayaran(p_pesanan_id BIGINT)
            RETURNS DECIMAL(12,2)
            DETERMINISTIC
            READS SQL DATA
            BEGIN
                DECLARE v_total_harga DECIMAL(12,2) DEFAULT 0;
                DECLARE v_total_bayar DECIMAL(12,2) DEFAULT 0;

                SELECT COALESCE(SUM(harga), 0) INTO v_total_harga FROM item_pesanan WHERE pesanan_id = p_pesanan_id;
                SELECT COALESCE(SUM(jumlah), 0) INTO v_total_bayar FROM pembayaran WHERE pesanan_id = p_pesanan_id;

                IF v_total_bayar >= v_total_harga THEN
                    RETURN 0;
                ELSE
                    RETURN v_total_harga - v_total_bayar;
                END IF;
            END;
        ');

        // 4. Stored Function: Menghitung status pembayaran (Belum Bayar, DP, Lunas)
        DB::unprepared("
            DROP FUNCTION IF EXISTS fn_status_pembayaran;
            CREATE FUNCTION fn_status_pembayaran(p_pesanan_id BIGINT)
            RETURNS VARCHAR(20)
            DETERMINISTIC
            READS SQL DATA
            BEGIN
                DECLARE v_total_harga DECIMAL(12,2) DEFAULT 0;
                DECLARE v_total_bayar DECIMAL(12,2) DEFAULT 0;

                SELECT COALESCE(SUM(harga), 0) INTO v_total_harga FROM item_pesanan WHERE pesanan_id = p_pesanan_id;
                SELECT COALESCE(SUM(jumlah), 0) INTO v_total_bayar FROM pembayaran WHERE pesanan_id = p_pesanan_id;

                IF v_total_harga = 0 THEN
                    RETURN 'Lunas';
                ELSEIF v_total_bayar >= v_total_harga THEN
                    RETURN 'Lunas';
                ELSEIF v_total_bayar > 0 THEN
                    RETURN 'DP';
                ELSE
                    RETURN 'Belum Bayar';
                END IF;
            END;
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::unprepared('DROP FUNCTION IF EXISTS fn_status_pembayaran;');
        DB::unprepared('DROP FUNCTION IF EXISTS fn_sisa_pembayaran;');
        DB::unprepared('DROP TRIGGER IF EXISTS tr_after_pemakaian_bahan_delete;');
        DB::unprepared('DROP TRIGGER IF EXISTS tr_after_pemakaian_bahan_insert;');
    }
};
