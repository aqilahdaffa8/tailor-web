# Design & Interface Guidelines

## Tailor Management System

### 1. Design Concept

Konsep utama:

**Modern Minimalist SaaS Dashboard (Clean, Focused, & High-Density)**

Antarmuka berfokus pada kecepatan kerja operasional, kejelasan data, dan kenyamanan visual bagi pemilik usaha jahit di desktop, laptop, maupun perangkat mobile/tablet di bengkel.

Prinsip desain:
* **Bersih & Fungsional**: Menghilangkan ornamen literal berlebihan (ikon gunting vintage, garis jahitan putus-putus/stitching line, titik dekoratif, dan istilah "atelier").
* **High Legibility**: Data pesanan, ukuran badan baju, dan nominal keuangan terbaca jelas seketika tanpa font mikro yang melelahkan mata.
* **Konsistensi Pola**: Seluruh halaman daftar data mengikuti pola tunggal: Judul, Toolbar (Pencarian & Tombol Aksi), Tabel Bergaris Tipis, Badge Status Pastel, dan Pagination.

---

### 2. Color System (True Neutral Charcoal & Putih)

Sistem warna mengadopsi palet monokromatis industrial netral murni (Zinc) tanpa bias semburat biru, dengan aksen status pastel yang tenang.

#### Primary Palette

| Token / Nama | Hex | Penggunaan |
| :--- | :--- | :--- |
| **Canvas Background** | `#FAFAFA` (`zinc-50`) | Latar belakang halaman aplikasi (netral murni) |
| **Surface / Card** | `#FFFFFF` (`white`) | Kontainer kartu data, modal, dan popover |
| **Sidebar Background** | `#18181B` (`zinc-900`) | Latar belakang sidebar navigasi (charcoal murni) |
| **Sidebar Menu Aktif** | `#27272A` (`zinc-800`) | Pil menu navigasi yang aktif di sidebar |
| **Border / Divider** | `#E4E4E7` (`zinc-200`) | Garis tepi kartu dan pembatas tabel |
| **Border Subtle** | `#F4F4F5` (`zinc-100`) | Garis baris tabel dan divider halus |
| **Charcoal Primary** | `#18181B` (`zinc-900`) | Teks heading, tombol aksi utama (CTA) |
| **Charcoal Hover** | `#27272A` (`zinc-800`) | Hover state tombol utama |
| **Text Secondary** | `#52525B` (`zinc-600`) | Teks isi paragraf, label formulir |
| **Text Muted** | `#71717A` (`zinc-500`) | Header tabel, metadata, placeholder |
| **Surface Subtle** | `#F4F4F5` (`zinc-100`) | Hover baris tabel, badge netral |

#### Status Palette (Soft Pastel Rings)

Status tidak menggunakan warna pekat yang berteriak, melainkan aksen pastel lembut dengan ring border transparan:

* **Selesai / Aktif**: `bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20`
* **Proses / Menunggu**: `bg-amber-50 text-amber-700 ring-1 ring-amber-600/20`
* **Terlambat / Batal / Error**: `bg-rose-50 text-rose-700 ring-1 ring-rose-600/20`
* **Draf / Netral**: `bg-slate-100 text-slate-700 ring-1 ring-slate-600/20`

---

### 3. Typography

Menggunakan **Inter** sebagai font tunggal di seluruh aplikasi.

* **Heading**: `font-semibold` (600) atau `font-bold` (700) dengan `tracking-tight`.
* **Body / Konten Tabel**: `text-sm` (14px) dengan warna `text-slate-700` atau `text-slate-900`.
* **Subteks / Metadata**: `text-xs` (12px) dengan warna `text-slate-500`.
* **Header Tabel**: `text-xs font-medium text-slate-500` (menggunakan *Sentence case*, bukan ALL CAPS renggang).
* **Angka Ukuran & Nominal Keuangan**: Menggunakan `tabular-nums` agar sejajar vertikal saat dibandingkan dalam tabel atau invoice.

> **Aturan**: Hapus seluruh penggunaan ukuran teks mikro (< 12px seperti 10px atau 11px) demi kemudahan membaca di layar mobile dan bengkel kerja.

---

### 4. Radius & Border Standards

Radius sudut distandarisasi secara ketat menjadi 2 tingkatan:

1. **Kontrol Interaktif**: `rounded-lg` (8px)
   - Tombol (Button).
   - Input formulir, select box, search bar.
   - Item menu aktif pada navigasi.
   - Badge status (atau `rounded-full` untuk pill status).
2. **Kontainer & Card**: `rounded-2xl` (16px) atau `rounded-xl` (12px)
   - Kartu metrik / widget dashboard.
   - Kontainer tabel utama.
   - Modal dialog dan panel slide-over.

---

### 5. Layout & Navigation Shell

#### Sidebar
* **Background**: Putih (`bg-white border-r border-slate-200`).
* **Brand Header**: Teks murni **TAILOR SYSTEM** (`text-sm font-bold tracking-tight text-slate-900`), tanpa ikon gunting dan tanpa tagline *"Digital Atelier"*.
* **Menu Navigasi**: Urutan 9 menu tetap sama persis:
  1. Dashboard
  2. Pelanggan
  3. Pesanan Jahit
  4. Library Pola
  5. Pengukuran
  6. Inventory Bahan
  7. Keuangan
  8. Tutorial
  9. Pengaturan
* **Item Menu**:
  - Menu Aktif: `bg-slate-100 text-slate-900 font-medium rounded-lg`.
  - Menu Normal: `text-slate-600 hover:text-slate-900 hover:bg-slate-50 rounded-lg`.
* **Footer Sidebar**: Profil pengguna (avatar inisial monokrom + nama + email) dan tombol logout minimalis.

#### Topbar
* Putih (`bg-white border-b border-slate-200 h-16`).
* Judul halaman di kiri (`text-lg font-bold text-slate-900`).
* Ikon notifikasi pengingat di kanan dengan badge counter bulat merah minimalis.

---

### 6. Template Standar Halaman Daftar (Unified List Pattern)

Semua halaman daftar (Pelanggan, Pesanan, Inventori, Pola, Keuangan) mengadopsi struktur yang seragam:

1. **Header Halaman**:
   - Judul halaman & deskripsi singkat.
2. **Toolbar Card**:
   - Pencarian di sisi kiri (`pl-9 pr-4 py-2 border-slate-200 rounded-lg text-sm`).
   - Tombol filter / pengaturan di tengah/kanan.
   - Tombol utama (+ Tambah Data) di ujung kanan (`bg-slate-900 text-white hover:bg-slate-800 rounded-lg text-sm font-medium px-4 py-2`).
3. **Table Container Card**:
   - Kontainer putih `bg-white border border-slate-200 rounded-2xl overflow-hidden shadow-2xs`.
   - Header tabel: `text-xs font-medium text-slate-500 bg-slate-50/75 border-b border-slate-200`.
   - Baris tabel: `divide-y divide-slate-100`, hover effect `hover:bg-slate-50/60`, padding vertikal nyaman `py-3.5`.
   - Badge status pastel dan tombol aksi ikon minimalis (`text-slate-400 hover:text-slate-700`).
4. **Footer Pagination**:
   - Info counter ("Menampilkan 1 - 10 dari 50 data") dan kontrol pagination rapi di bagian bawah kartu.

---

### 7. Form & Detail View Standards

* **Label Form**: `text-sm font-medium text-slate-700 mb-1.5` (sentence case).
* **Input Text / Select**: `rounded-lg border-slate-200 focus:border-slate-900 focus:ring-1 focus:ring-slate-900 text-sm py-2 px-3`.
* **Action Buttons**:
  - Simpan / Konfirmasi: `bg-slate-900 text-white hover:bg-slate-800 rounded-lg px-4 py-2 text-sm font-medium`.
  - Batal / Sekunder: `border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 rounded-lg px-4 py-2 text-sm font-medium`.

---

### 8. Motion & Micro-interactions

* Durasi transisi cepat: `150ms` sampai `200ms` (`ease-out`).
* Menghormati `prefers-reduced-motion`.
* Animasi hanya untuk feedback fungsional (dropdown buka-tutup, modal fade-in, hover feedback). Tidak ada animasi pemotongan kertas atau benang dekoratif.
