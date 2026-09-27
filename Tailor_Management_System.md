# Tailor Management System

## 1. Ringkasan Project

**Tailor Management System** adalah aplikasi manajemen usaha penjahit untuk membantu mengelola seluruh proses jasa jahit dari pelanggan, pesanan, pola, pengukuran, pengerjaan, pembayaran, inventory, sampai laporan sederhana.

Aplikasi ditujukan untuk **usaha penjahit yang dikelola oleh satu orang**.

Sistem ini **bukan e-commerce dan bukan toko pakaian**. Fokus utamanya adalah **jasa**, seperti:

- Membuat pakaian
- Permak
- Memotong bahan
- Mengecilkan pakaian
- Membesarkan pakaian
- Mengganti resleting
- Mengganti kancing
- Jasa jahit lainnya

---

# 2. Tujuan Sistem

Sistem dibuat untuk:

1. Mempermudah pengelolaan pesanan jahit.
2. Menyimpan data pelanggan dan riwayat pesanan.
3. Menyediakan library pola standar berdasarkan jenis pakaian dan ukuran.
4. Menyimpan pengukuran tubuh pelanggan secara terpisah dari library pola.
5. Mengelola progress pengerjaan setiap pesanan.
6. Mengelola pembayaran dan POS khusus jasa.
7. Mengelola stok bahan dan inventaris peralatan.
8. Menyediakan ringkasan keuangan usaha.
9. Menyimpan tutorial/knowledge base untuk kebutuhan penjahit.
10. Menyediakan reminder untuk pekerjaan penting.

---

# 3. Model Bisnis

## 3.1 Karakteristik Usaha

- Dikelola oleh satu orang.
- Tidak memiliki pegawai yang perlu dikelola melalui sistem.
- Tidak menjual produk pakaian jadi.
- Tidak membutuhkan supplier management.
- Semua bahan dan alat dibeli sendiri oleh pemilik.
- Fokus pada jasa jahit dan pekerjaan yang berhubungan dengan penjahitan.

## 3.2 Yang Tidak Termasuk Sistem

Fitur berikut **tidak diperlukan**:

- Penjualan produk pakaian jadi.
- Katalog produk retail.
- Keranjang belanja produk.
- Supplier management.
- Manajemen pegawai/role internal yang kompleks.

---

# 4. Modul Sistem

## 4.1 Dashboard

Dashboard menjadi ringkasan kondisi usaha.

### Informasi

- Pesanan aktif.
- Pesanan mendekati deadline.
- Pesanan terlambat.
- Pembayaran belum lunas.
- Pendapatan.
- Pengeluaran.
- Stok bahan menipis.

Dashboard berfungsi sebagai **overview dan shortcut**, bukan tempat utama untuk mengelola seluruh data.

---

## 4.2 Pelanggan

Digunakan untuk menyimpan data pelanggan.

### Data pelanggan

- Nama.
- Nomor HP.
- Alamat.
- Catatan.
- Riwayat pesanan.
- Total transaksi.

### Flow

```text
Pelanggan
  ↓
Tambah Pelanggan
  ↓
Nama + No. HP + Alamat + Catatan
  ↓
Simpan
  ↓
Detail Pelanggan
  ├── Informasi
  ├── Riwayat Pesanan
  └── Total Transaksi
```

---

## 4.3 Pesanan Jahit

Pesanan jahit merupakan **pusat utama aplikasi**.

Semua pekerjaan jasa masuk melalui modul ini.

### Struktur Pesanan: Header dan Item

Satu pesanan **dapat berisi lebih dari satu jenis jasa sekaligus** (contoh: pelanggan membuat setelan kemeja + celana dalam satu kunjungan, atau permak + ganti kancing dalam satu pesanan).

Karena itu, Pesanan dirancang dengan struktur **header - item**, bukan satu jasa per pesanan:

```text
Pesanan (Header)
  - Nomor pesanan
  - Pelanggan
  - Tanggal pesan
  - Deadline keseluruhan
  - Catatan umum
  - Status pembayaran (agregat)
  │
  └── Item Pesanan (1..n)
        - Jenis jasa
        - Pola yang digunakan (opsional)
        - Pengukuran yang digunakan (opsional)
        - Harga item
        - Progress pengerjaan item (independen per item)
        - Bahan yang digunakan (opsional)
        - Deadline item (opsional, boleh ikut deadline header)
        - Catatan item
        - Foto referensi item
```

Konsekuensi desain:

- **Progress pengerjaan** dilacak per **item**, bukan per header pesanan, karena setiap item bisa berada di tahap berbeda (misal kemeja sudah "Finishing", celana masih "Pemotongan").
- **Harga dan pembayaran** tetap dikelola di level header (total = jumlah seluruh item), agar DP dan pelunasan tidak perlu dipecah per item.
- **Pola dan pengukuran** direlasikan ke item, bukan ke header, karena satu item = satu jenis pakaian/jasa dengan pola dan ukurannya sendiri.
- Pesanan sederhana (satu jasa saja) tetap valid, cukup dengan satu item.

### Informasi Pesanan (Header)

- Nomor pesanan.
- Pelanggan.
- Tanggal pesan.
- Deadline.
- Status pembayaran (agregat dari seluruh item).
- Catatan umum.

### Informasi Item Pesanan

- Jenis jasa.
- Detail pekerjaan.
- Pola yang digunakan.
- Pengukuran yang digunakan.
- Harga.
- Bahan yang digunakan (opsional, untuk dikurangi dari stok).
- Progress pengerjaan.
- Catatan.
- Foto referensi jika diperlukan.

### Jenis jasa

Jenis jasa dapat dibuat dan dikelola sendiri oleh pemilik, misalnya:

- Jahit kemeja.
- Jahit celana.
- Permak.
- Potong.
- Mengecilkan pakaian.
- Membesarkan pakaian.
- Ganti resleting.
- Ganti kancing.
- Jasa lainnya.

### Flow membuat pesanan

```text
Pesanan
  ↓
+ Pesanan Baru
  ↓
Pilih Pelanggan
  ↓
Pilih Jenis Jasa
  ↓
Masukkan Detail
  ↓
Pilih / Buat Pola
  ↓
Gunakan / Input Pengukuran
  ↓
Tentukan Deadline
  ↓
Tentukan Harga
  ↓
Pembayaran DP (Opsional)
  ↓
Pesanan Dibuat
```

---

## 4.4 Progress Pengerjaan

Setiap pesanan mempunyai progress pengerjaan.

### Alur standar

```text
Pesanan Dibuat
  ↓
Menunggu Dikerjakan
  ↓
Pengukuran
  ↓
Pemotongan
  ↓
Penjahitan
  ↓
Finishing
  ↓
Selesai
  ↓
Diambil Pelanggan
```

Tidak semua jasa harus menggunakan seluruh tahapan.

Contoh jasa ganti resleting:

```text
Pesanan
  ↓
Dikerjakan
  ↓
Finishing
  ↓
Selesai
  ↓
Diambil
```

Artinya workflow progress harus **fleksibel berdasarkan jenis jasa**.

### Mekanisme Fleksibilitas (Template Tahapan per Jenis Jasa)

Fleksibilitas ini diimplementasikan melalui **template tahapan** yang melekat pada tiap **Jenis Jasa**, bukan status pengerjaan yang bebas ditentukan manual setiap kali:

```text
Jenis Jasa
  └── Template Tahapan (daftar tahapan berurutan)
        Contoh "Jahit Kemeja":
          1. Menunggu Dikerjakan
          2. Pengukuran
          3. Pemotongan
          4. Penjahitan
          5. Finishing
          6. Selesai
          7. Diambil Pelanggan

        Contoh "Ganti Resleting":
          1. Dikerjakan
          2. Finishing
          3. Selesai
          4. Diambil
```

Saat sebuah **Item Pesanan** dibuat, sistem menyalin template tahapan dari jenis jasa terkait menjadi progress khusus milik item tersebut. Ini membuat:

- Perubahan template di kemudian hari **tidak mengubah** riwayat pesanan yang sudah berjalan (snapshot per item).
- Pemilik usaha dapat menambah/mengedit tahapan lewat modul **Pengaturan > Jenis Jasa**, tanpa perlu mengubah kode program.
- Progress tiap item pesanan berjalan independen dan bisa maju/mundur sesuai kondisi nyata di lapangan.

---

## 4.5 Library Pola

Library pola merupakan salah satu fitur utama dan pembeda aplikasi.

### Konsep penting

Library pola **bukan tempat menyimpan ukuran tubuh pelanggan**.

Library pola menyimpan **pola dan ukuran standar berdasarkan jenis pakaian dan size**.

### Contoh

```text
Kemeja
 └── Formal
      ├── S
      ├── M
      ├── L
      ├── XL
      └── XXL
```

### Detail pola

Contoh `Kemeja Formal - M`:

- Panjang baju.
- Lebar dada.
- Lebar bahu.
- Panjang lengan.
- Lingkar kerah.
- Detail ukuran lainnya.
- Foto pola.
- File pola jika tersedia.
- Catatan.
- Versi pola jika diperlukan.

### Flow

```text
Library Pola
  ↓
Pilih Kategori
  ↓
Pilih Jenis Pakaian
  ↓
Pilih Size
  ↓
Detail Pola
```

### Contoh kategori

- Kemeja.
- Celana.
- Dress.
- Gamis.
- Jas.
- Blouse.
- Kategori lainnya.

### Hubungan dengan Pesanan

```text
Pesanan Baru
  ↓
Jenis Pakaian
  ↓
Gunakan Pola?
  ├── Ya → Pilih Pola → Pilih Size → Masuk ke Pesanan
  └── Tidak → Buat / Input Manual
```

---

## 4.6 Pengukuran Pelanggan

Pengukuran pelanggan dibuat **terpisah** dari Library Pola.

### Perbedaan

**Library Pola**

> Ukuran standar pola berdasarkan jenis pakaian dan size.

**Pengukuran Pelanggan**

> Ukuran tubuh pelanggan tertentu.

### Contoh

```text
Pelanggan: Budi

Lingkar Dada   : 98 cm
Lingkar Pinggang: 88 cm
Lebar Bahu     : 44 cm
Panjang Lengan : 60 cm
...
```

### Flow

```text
Pesanan
  ↓
Pengukuran
  ↓
Pilih Pelanggan
  ↓
Gunakan Pengukuran Terakhir?
  ├── Ya → Gunakan / Edit
  └── Tidak → Input Pengukuran Baru
  ↓
Simpan
```

Data pengukuran terakhir dapat digunakan kembali ketika pelanggan datang di lain waktu.

---

## 4.7 Pembayaran / POS Jasa

POS dibuat khusus untuk **jasa**, bukan retail product POS.

### Yang dikelola

- Jasa.
- Harga.
- DP.
- Pelunasan.
- Status pembayaran.
- Metode pembayaran.
- Invoice.
- Riwayat pembayaran.

### Status pembayaran

```text
Belum Bayar
  ↓
DP
  ↓
Lunas
```

### Pembayaran Bertahap (Multiple Partial Payment)

Pembayaran tidak dibatasi hanya "DP lalu Lunas" dua langkah. Sistem mencatat pembayaran sebagai **riwayat transaksi**, sehingga mendukung pembayaran bertahap lebih dari sekali (contoh: DP awal, DP tambahan, lalu pelunasan).

```text
Pesanan (Total: Rp500.000)
  ├── Pembayaran 1: Rp200.000 (DP)      → Sisa Rp300.000
  ├── Pembayaran 2: Rp100.000 (Cicilan) → Sisa Rp200.000
  └── Pembayaran 3: Rp200.000 (Pelunasan) → Sisa Rp0 (Lunas)
```

Status pembayaran pesanan dihitung otomatis dari total pembayaran yang sudah masuk dibandingkan harga total, bukan disimpan sebagai pilihan manual "DP/Lunas" yang mudah tidak sinkron.

### Flow

```text
Pesanan
  ↓
Jasa
  ↓
Harga
  ↓
Input Pembayaran (dapat diulang beberapa kali)
  ↓
Invoice
  ↓
Status Pembayaran (dihitung otomatis dari total dibayar vs total tagihan)
```

Saat pekerjaan selesai:

```text
Pesanan Selesai
  ↓
Cek Sisa Pembayaran
  ├── Belum Lunas → Bayar
  └── Lunas → Siap Diambil
  ↓
Diambil Pelanggan
```

---

## 4.8 Inventory Bahan & Peralatan

Inventory digunakan untuk kebutuhan internal usaha.

### Bahan

Contoh:

- Kain.
- Benang.
- Resleting.
- Kancing.
- Interfacing.
- Bahan lainnya.

Bahan memiliki stok dan dapat berubah ketika digunakan.

### Peralatan

Contoh:

- Mesin jahit.
- Mesin obras.
- Gunting.
- Meteran.
- Peralatan lainnya.

Peralatan tidak diperlakukan seperti stok bahan habis pakai.

### Flow bahan

```text
Inventory
  ↓
Bahan
  ↓
Pilih Bahan
  ↓
Lihat Stok
```

Ketika bahan digunakan:

```text
Pesanan
  ↓
Gunakan Bahan
  ↓
Catat Pemakaian
  ↓
Stok Berkurang
```

Inventory dapat terhubung dengan pesanan agar penggunaan bahan dapat dilacak.

---

## 4.9 Keuangan

Modul keuangan dibuat sederhana dan fokus pada kebutuhan usaha kecil.

### Pendapatan

Pendapatan berasal dari pembayaran pesanan.

```text
Pesanan
  ↓
Pembayaran
  ↓
Pendapatan
```

### Pengeluaran

Pengeluaran dicatat manual ketika ada pembelian atau biaya usaha.

Contoh:

- Pembelian kain.
- Pembelian benang.
- Pembelian kancing.
- Perawatan mesin.
- Pengeluaran lainnya.

### Flow

```text
Keuangan
  ↓
+ Pengeluaran
  ↓
Kategori
  ↓
Nominal
  ↓
Catatan
  ↓
Simpan
```

### Ringkasan

```text
Pendapatan
- Pengeluaran
= Selisih
```

Sistem tidak dirancang sebagai software akuntansi kompleks.

---

## 4.10 Tutorial / Knowledge Base

Tempat menyimpan materi yang berhubungan dengan kegiatan menjahit.

### Konten

- Video tutorial.
- Artikel.
- Gambar.
- PDF.
- Tips dan trik.

### Kategori

- Membuat pola.
- Teknik menjahit.
- Permak.
- Finishing.
- Perawatan mesin.
- Kategori lainnya.

### Flow

```text
Tutorial
  ↓
Kategori
  ↓
Pilih Tutorial
  ↓
Video / Artikel / PDF
```

Tutorial merupakan **fitur pendukung**, bukan bagian utama dari transaksi.

---

## 4.11 Reminder

Reminder digunakan untuk membantu pemilik usaha mengingat pekerjaan penting.

### Reminder

- Pesanan mendekati deadline.
- Pesanan terlambat.
- Pembayaran belum lunas.
- Pesanan siap diambil.
- Stok bahan menipis.

### Flow

```text
Sistem
  ↓
Mendeteksi kondisi
  ↓
Reminder
  ↓
Dashboard
```

---

## 4.12 Pengaturan

Digunakan untuk konfigurasi aplikasi.

### Isi

- Profil usaha.
- Jenis jasa.
- Status pesanan.
- Kategori pola.
- Kategori bahan.
- Pengaturan invoice.
- Backup data.
- Pengaturan aplikasi lainnya.

---

# 5. Flow Utama Sistem

Workflow utama aplikasi adalah:

```text
Pelanggan
  ↓
Pesanan
  ↓
Pola / Pengukuran
  ↓
Pengerjaan
  ↓
Pembayaran
  ↓
Selesai
  ↓
Diambil Pelanggan
```

### Sistem pendukung

```text
              ┌──────────────┐
              │  INVENTORY   │
              └──────┬───────┘
                     ↓
                  PESANAN

              ┌──────────────┐
              │  KEUANGAN    │
              └──────┬───────┘
                     ↑
                PEMBAYARAN
                + PENGELUARAN

              ┌──────────────┐
              │  TUTORIAL    │
              └──────────────┘
```

---

# 6. Prioritas Fitur

## MVP

1. Dashboard.
2. Pelanggan.
3. Pesanan Jahit.
4. Progress Pengerjaan.
5. Library Pola.
6. Pengukuran Pelanggan.
7. Pembayaran / POS Jasa.
8. Inventory.

## Phase 2

1. Keuangan.
2. Reminder.
3. Pengaturan yang lebih lengkap.

## Phase 3

1. Tutorial / Knowledge Base.
2. Backup dan fitur tambahan.

---

# 7. Arsitektur Teknologi

Karena sistem akan memiliki **Web Frontend** dan **Mobile Frontend**, tetapi hanya ingin menggunakan **satu backend**, arsitektur yang dipilih adalah:

```text
                     ┌──────────────────┐
                     │      MySQL       │
                     │                  │
                     │ Trigger          │
                     │ Function         │
                     │ Procedure        │
                     │ Transaction      │
                     │ COMMIT           │
                     │ ROLLBACK         │
                     └────────▲─────────┘
                              │
                     ┌────────┴─────────┐
                     │      Laravel     │
                     │    Web + API     │
                     │                  │
                     │ Business Logic   │
                     │ Authentication   │
                     │ Validation       │
                     │ REST API         │
                     └───────┬────┬─────┘
                             │    │
                         Web │    │ API
                             │    │
                  ┌──────────▼┐  ┌▼────────────┐
                  │  Browser  │  │   Android   │
                  │ Blade     │  │ Kotlin      │
                  │ Livewire  │  │ Compose     │
                  │ Tailwind  │  │             │
                  └───────────┘  └─────────────┘
```

---

# 8. Stack Teknologi

## Backend

- **Laravel 13**
- REST API
- Authentication
- Business Logic
- Validation
- Database Access

## Web Frontend

- **Blade**
- **Livewire**
- **Tailwind CSS**
- **Alpine.js**

Web frontend dan backend Laravel berada dalam **satu repository dan satu aplikasi Laravel**.

## Mobile Frontend

- **Kotlin**
- **Jetpack Compose**
- ViewModel
- Repository
- API Client

## Database

- **MySQL**

Database harus mendukung dan memanfaatkan fitur:

- Trigger.
- Stored Function.
- Stored Procedure.
- Transaction.
- COMMIT.
- ROLLBACK.
- Constraint dan foreign key.
- Index.

---

# 9. Jumlah Repository

Project menggunakan **2 repository**.

```text
GitHub
│
├── tailor-web
│   └── Laravel
│       ├── Web Frontend
│       ├── Backend
│       └── REST API
│
└── tailor-mobile
    └── Kotlin
        └── Jetpack Compose
```

## Repository 1: `tailor-web`

Berisi seluruh aplikasi Laravel.

Laravel menangani:

- Web frontend.
- REST API.
- Business logic.
- Authentication.
- Validation.
- Database access.
- File handling.
- Transaction.

## Repository 2: `tailor-mobile`

Berisi aplikasi Android Kotlin.

Aplikasi mobile hanya menjadi **client** yang mengakses REST API Laravel.

Tidak ada Laravel di dalam mobile repository.

---

# 10. Cara Web dan Mobile Terhubung

## Web

Web menggunakan Laravel secara langsung:

```text
Browser
  ↓
Laravel
  ↓
MySQL
```

Web dapat menggunakan Blade + Livewire untuk UI.

## Mobile

Mobile mengakses REST API Laravel:

```text
Android
  ↓
HTTP Request
  ↓
Laravel API
  ↓
MySQL
  ↓
JSON Response
  ↓
Android
```

### Contoh

Android meminta daftar pesanan:

```http
GET /api/orders
```

Laravel memproses request dan mengembalikan JSON.

Contoh response:

```json
{
  "data": [
    {
      "id": 1,
      "customer": "Budi",
      "service": "Jahit Kemeja",
      "status": "in_progress"
    }
  ]
}
```

Android kemudian mengubah response tersebut menjadi state dan menampilkannya menggunakan Jetpack Compose.

---

# 11. Contoh Sinkronisasi Data Web dan Mobile

## Pesanan dibuat melalui Web

```text
Web
 ↓
Laravel
 ↓
MySQL
```

Ketika Android meminta data:

```text
Android
 ↓
GET /api/orders
 ↓
Laravel
 ↓
MySQL
 ↓
JSON
 ↓
Android
```

Pesanan yang dibuat melalui Web akan muncul di Android karena keduanya menggunakan **backend dan database yang sama**.

## Pesanan dibuat melalui Android

```text
Android
 ↓
POST /api/orders
 ↓
Laravel
 ↓
MySQL
```

Ketika Web dibuka:

```text
Browser
 ↓
Laravel
 ↓
MySQL
 ↓
Pesanan tampil
```

---

# 12. Deployment dan Hosting

Tidak perlu melakukan deploy dua Laravel.

### Yang dideploy

```text
1 × Laravel Application
1 × MySQL Database
1 × Domain (opsional)
```

### Yang tidak diperlukan

```text
2 × Laravel
2 × Backend
2 × Database
```

Arsitektur production:

```text
                INTERNET
                   │
            ┌──────▼──────┐
            │   Laravel   │
            │   Web + API │
            └──────┬──────┘
                   │
                 MySQL
                   ▲
          ┌────────┴────────┐
          │                 │
       Browser           Android
        Web UI            Kotlin
```

Mobile tidak membutuhkan hosting Laravel sendiri. Aplikasi Android hanya perlu terhubung ke URL API Laravel yang sudah online.

---

# 13. Prinsip Arsitektur

## Single Source of Truth

Web dan mobile tidak mempunyai business logic yang berbeda.

Business logic utama berada di Laravel.

```text
Web ──────┐
          ↓
       Laravel
          ↓
        MySQL
          ↑
Mobile ───┘
```

## Tidak Duplikasi Backend

Jangan membuat:

```text
Web → Laravel Backend A
Mobile → Laravel Backend B
```

Gunakan:

```text
Web ──────┐
          ↓
       1 Laravel
          ↓
        MySQL
          ↑
Mobile ───┘
```

## Pemisahan Client dan Server

- Web = client/web UI Laravel.
- Mobile = client Android Kotlin.
- Laravel = server/backend/API.
- MySQL = database.

---

# 14. Model Data (ERD Konseptual)

Bagian ini merangkum entitas utama dan relasinya sebagai acuan sebelum pembuatan skema database detail.

```text
Pelanggan (1) ──< (N) Pesanan
Pelanggan (1) ──< (N) Pengukuran

Pesanan (1) ──< (N) Item_Pesanan
Pesanan (1) ──< (N) Pembayaran

Jenis_Jasa (1) ──< (N) Item_Pesanan
Jenis_Jasa (1) ──< (N) Template_Tahapan

Item_Pesanan (1) ──< (N) Progress_Item   (snapshot dari Template_Tahapan)
Item_Pesanan (N) ──> (1) Pola_Detail          [opsional]
Item_Pesanan (N) ──> (1) Pengukuran           [opsional]
Item_Pesanan (1) ──< (N) Pemakaian_Bahan

Kategori_Pola (1) ──< (N) Jenis_Pakaian
Jenis_Pakaian (1) ──< (N) Pola_Detail  (per size)

Bahan (1) ──< (N) Pemakaian_Bahan
Bahan (1) ──< (N) Riwayat_Stok

Pengeluaran (berdiri sendiri, terhubung opsional ke Bahan/Peralatan)
Peralatan (berdiri sendiri, tidak dikurangi seperti stok habis pakai)

User (1) ──< (N) Riwayat_Aktivitas   [audit trail login web/mobile]
```

### Catatan penting relasi

- **Item_Pesanan** adalah entitas penghubung utama: menyatukan Jenis Jasa, Pola, Pengukuran, Bahan, dan Progress dalam satu baris pekerjaan.
- **Pola_Detail** dan **Pengukuran** sengaja terpisah (sesuai Bab 4.5 dan 4.6) tapi keduanya bisa dirujuk dari Item_Pesanan yang sama.
- **Template_Tahapan** disalin menjadi **Progress_Item** saat item dibuat, agar histori pesanan tidak berubah jika template diedit kemudian (lihat Bab 4.4).
- **Pembayaran** dicatat sebagai tabel riwayat (bukan satu kolom status), agar mendukung pembayaran bertahap (lihat Bab 4.7).

---

# 15. Use Case Fitur Lanjutan Database (MySQL)

Requirement penggunaan Trigger, Function, Procedure, dan Transaction (Bab 8) diberi contoh use case konkret agar tidak menjadi requirement teknis tanpa kebutuhan nyata:

| Fitur DB | Use Case |
|---|---|
| **Trigger** | Mengurangi stok `Bahan` otomatis setiap kali baris baru ditambahkan ke `Pemakaian_Bahan`. Mencatat `Riwayat_Stok` otomatis setiap kali stok berubah. |
| **Stored Function** | Menghitung total sisa pembayaran suatu pesanan (`total_harga - SUM(pembayaran)`). Menghitung status pembayaran (Belum Bayar/DP/Lunas) berdasarkan hasil function tersebut. |
| **Stored Procedure** | Proses "Buat Pesanan Baru" yang sekaligus insert ke `Pesanan`, `Item_Pesanan`, dan `Progress_Item` (copy dari template) dalam satu pemanggilan. |
| **Transaction + COMMIT/ROLLBACK** | Saat pesanan dibuat dan bahan langsung dipakai: insert pesanan, insert pemakaian bahan, dan update stok harus berhasil bersama-sama. Jika salah satu gagal (misal stok tidak cukup), seluruh proses di-ROLLBACK agar data tidak setengah tersimpan. |
| **Constraint & Foreign Key** | Mencegah `Item_Pesanan` merujuk ke `Pola_Detail` atau `Pengukuran` milik pelanggan yang berbeda dari pelanggan pesanan tersebut. |

---

# 16. Autentikasi & Manajemen Akses

Meskipun usaha dikelola satu orang, akses dilakukan dari dua platform (Web dan Mobile) sehingga tetap dibutuhkan manajemen sesi yang benar:

- Autentikasi menggunakan **Laravel Sanctum** untuk menerbitkan token API bagi Android, dan session standar Laravel untuk Web.
- Struktur tabel `users` tetap disiapkan secara generik (bukan hardcode satu akun), agar jika di masa depan pemilik ingin menambahkan asisten/admin dengan akses terbatas, tidak diperlukan migrasi besar — cukup menambah baris pengguna dan (opsional) role sederhana.
- Untuk MVP, cukup satu role implisit ("Owner") tanpa sistem permission kompleks, sesuai prinsip di Bab 3.

---

# 17. Strategi Penyimpanan File (Storage)

Beberapa modul menyimpan file (foto referensi pesanan, foto/file pola, video/artikel/PDF tutorial). Strategi penyimpanan perlu ditentukan sejak awal karena berdampak pada Bab 12 (Deployment):

- **MVP/awal**: file disimpan di local disk server melalui Laravel Filesystem (`storage/app/public`), cukup untuk skala satu pengguna.
- **Rekomendasi jangka menengah**: menggunakan storage S3-compatible (contoh: AWS S3, atau alternatif lokal seperti MinIO/Cloudflare R2) agar tidak bergantung pada disk server tunggal dan lebih mudah di-backup.
- Batasan ukuran unggah (contoh: foto maksimal 5MB, video tutorial sebaiknya berupa tautan eksternal seperti YouTube daripada file mentah agar tidak membebani server) perlu ditetapkan di Pengaturan.

---

# 18. Kesimpulan Arsitektur Final

```text
PROJECT
│
├── Repository 1: tailor-web
│   └── Laravel
│       ├── Blade
│       ├── Livewire
│       ├── Tailwind CSS
│       ├── Alpine.js
│       ├── REST API
│       ├── Business Logic
│       └── Database Access
│
├── Repository 2: tailor-mobile
│   └── Kotlin
│       └── Jetpack Compose
│
└── Database
    └── MySQL
        ├── Trigger
        ├── Function
        ├── Procedure
        ├── Transaction
        ├── COMMIT
        └── ROLLBACK
```

### Keputusan final

- **2 repository.**
- **1 Laravel.**
- **1 backend.**
- **1 MySQL database.**
- **1 web frontend.**
- **1 mobile frontend.**
- Web dan mobile menggunakan backend Laravel yang sama.
- Tidak ada supplier management.
- Tidak ada penjualan produk jadi.
- Library Pola menyimpan pola standar berdasarkan jenis pakaian dan size.
- Pengukuran pelanggan disimpan sebagai modul terpisah.
- POS hanya untuk jasa.
- Inventory fokus pada bahan dan peralatan internal.
- Pesanan berstruktur header-item, mendukung banyak jenis jasa dalam satu pesanan.
- Progress pengerjaan dilacak per item, berdasarkan template tahapan per jenis jasa.
- Pembayaran dicatat sebagai riwayat, mendukung pembayaran bertahap lebih dari dua langkah.
- Trigger/Function/Procedure/Transaction MySQL dipakai untuk kasus nyata: potong stok otomatis, hitung sisa pembayaran, dan proses pembuatan pesanan yang atomik.
- Autentikasi via Laravel Sanctum (API token untuk mobile) dan session Laravel (web), dengan tabel `users` generik untuk pertumbuhan di masa depan.
- File (foto, pola, tutorial) disimpan di local disk untuk MVP, dengan opsi migrasi ke storage S3-compatible.
