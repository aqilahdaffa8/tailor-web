# Design & Motion Direction

## Tailor Management System

### 1. Design Concept

Konsep utama:

**Modern SaaS Dashboard + Digital Atelier + Tailoring Pattern Book**

Desain harus terasa modern, profesional, hangat, dan jelas memiliki identitas dunia penjahitan.

Gunakan elemen seperti:

* Garis jahitan putus-putus.
* Garis pola pakaian.
* Gunting.
* Kain.
* Benang dan jarum.
* Meteran.
* Kancing.
* Hanger.
* Sketsa pakaian.
* Pattern paper.

Elemen tailoring digunakan sebagai aksen visual, bukan memenuhi seluruh interface.

---

# 2. Color System

### Primary Palette

| Nama       | Hex       | Penggunaan                   |
| ---------- | --------- | ---------------------------- |
| Ivory      | `#F7F4EE` | Background utama             |
| White      | `#FFFFFF` | Card dan surface             |
| Charcoal   | `#29302D` | Text utama, sidebar, primary |
| Dark Olive | `#38443D` | Primary alternative          |
| Warm Taupe | `#9A816B` | Secondary accent             |
| Warm Brown | `#A8754F` | Tailoring accent             |
| Beige      | `#E7DED2` | Background secondary         |
| Soft Green | `#6F8A78` | Success, completed           |
| Warm Amber | `#C49A52` | Warning, deadline            |
| Muted Red  | `#B86B67` | Error, overdue               |

### Warna aksen tambahan

Untuk elemen visual tertentu dapat digunakan secara terbatas:

* Terracotta: `#B9654A`
* Dusty Blue: `#71879A`
* Dusty Rose: `#B58B86`

Jangan menggunakan seluruh warna secara bersamaan. Gunakan **Charcoal/Dark Olive + Ivory + Warm Brown/Taupe** sebagai identitas utama, lalu warna status sebagai aksen.

### Karakter warna

Visual harus terasa seperti:

* Kertas pola.
* Kain linen.
* Ruang kerja atelier.
* Kayu dan alat jahit.
* Material fashion premium.

Hindari warna neon atau kombinasi yang terlalu terang karena akan menghilangkan kesan atelier.

---

# 3. Tailoring Visual Elements

### Stitching Line

Gunakan garis jahitan sebagai salah satu identitas visual utama.

Variasi:

* Dashed line.
* Dotted line.
* Double stitching.
* Curved stitching.
* Garis mengikuti bentuk pola pakaian.

Gunakan pada:

* Divider.
* Card tertentu.
* Timeline.
* Progress.
* Banner.
* Empty state.

Jangan menggunakan stitching border pada semua component.

### Pattern Line

Gunakan garis seperti pola potongan pakaian pada:

* Background.
* Section divider.
* Hero area.
* Empty state.
* Pattern Library.
* Detail pesanan.

### Tailoring Objects

Gunakan secara selektif:

* Gunting.
* Meteran.
* Benang.
* Jarum.
* Kancing.
* Kain.
* Hanger.
* Mesin jahit.
* Potongan pola.

---

# 4. Motion Design

Motion harus menjadi bagian dari identitas aplikasi.

Prinsip animasi:

**Smooth, cepat, subtle, premium, dan functional.**

Animasi tidak boleh memperlambat pekerjaan pengguna.

---

## 4.1 Tailoring Page Transition

Animasi utama ketika berpindah halaman.

Konsep:

1. Muncul lembar kertas pola.
2. Terdapat garis potong putus-putus.
3. Gunting bergerak mengikuti garis.
4. Kertas terpotong.
5. Potongan kertas terbuka atau bergeser.
6. Halaman baru muncul di balik kertas.
7. Potongan kertas keluar dari viewport.

Visual:

```text
Page A
   ↓
Pattern Paper
   ↓
- - - - - - - - -
       ✂
- - - - - - - - -
   ↓
Paper Cut
   ↓
Page B
```

Durasi ideal sekitar **400 sampai 700ms**.

Gunakan transition yang lebih singkat untuk navigasi kecil agar tidak terasa mengganggu.

---

## 4.2 Stitching Progress Animation

Progress pengerjaan menggunakan garis jahitan.

Contoh:

```text
✓ Pesanan
      • - • - •
✓ Pengukuran
      • - • - •
● Pemotongan
      • - • - •
○ Penjahitan
      • - • - •
○ Finishing
```

Saat status berubah:

* Node aktif muncul dengan animasi kecil.
* Garis jahitan bergerak.
* Status sebelumnya mendapatkan checkmark.
* Progress terasa seperti sedang dijahit.

---

## 4.3 Sewing Thread Button Interaction

Untuk beberapa primary action, gunakan micro-interaction bertema benang.

Contoh:

* Tombol "Simpan" memiliki garis benang yang bergerak.
* Setelah berhasil, garis benang menyelesaikan satu loop.
* Tombol kemudian berubah menjadi state sukses.

Digunakan secara terbatas pada action penting.

---

## 4.4 Pattern Upload Animation

Saat pengguna mengupload pola:

```text
Upload Pattern
      ↓
Paper Sheet muncul
      ↓
Pattern preview ditempatkan
      ↓
Paper pinned / stitched
      ↓
Upload selesai
```

Efek dapat dibuat seperti lembar pola ditempel di papan kerja.

---

## 4.5 Card Hover

Pada desktop:

* Card sedikit naik.
* Shadow bertambah sangat halus.
* Stitching line dapat bergerak beberapa pixel.
* Foto pola dapat melakukan zoom sangat kecil.

Jangan menggunakan efek hover yang berlebihan.

---

## 4.6 Order Status Animation

Ketika pesanan berubah status:

```text
Pemotongan
    ↓
Penjahitan
```

Node timeline berpindah dengan animasi smooth dan garis progress bergerak mengikuti status baru.

Tujuannya membuat perubahan status terasa seperti sebuah proses fisik.

---

## 4.7 Empty State Animation

Empty state menggunakan ilustrasi tailor sederhana.

Contoh:

* Gunting membuka dan menutup secara halus.
* Benang bergerak perlahan.
* Kertas pola bergoyang sedikit.
* Meteran melakukan gerakan kecil.
* Mesin jahit memiliki motion loop kecil.

Animasi harus sangat subtle.

---

## 4.8 Modal / Detail Sheet Animation

Modal detail dapat muncul seperti **lembar pola yang dibuka**.

Efek:

* Paper sheet slide-up.
* Sedikit rotation correction.
* Shadow muncul perlahan.
* Stitching line mengikuti bagian tepi.

Ini dapat digunakan untuk detail pola, detail pelanggan, atau detail pesanan.

---

## 4.9 Notification Animation

Reminder menggunakan micro-interaction kecil:

* Icon gunting bergerak ketika ada deadline.
* Bell memiliki subtle bounce.
* Kartu reminder muncul dengan slide/fade.
* Tidak menggunakan shake berlebihan.

---

# 5. Motion Tokens

Semua animasi harus menggunakan token terpusat agar motion konsisten di seluruh aplikasi.

### Duration

| Token            |   Value | Penggunaan                           |
| ---------------- | ------: | ------------------------------------ |
| `motion-instant` |   `0ms` | Reduced motion / state tanpa animasi |
| `motion-fast`    | `120ms` | Hover, icon, micro interaction       |
| `motion-normal`  | `200ms` | Button, card, tooltip                |
| `motion-medium`  | `320ms` | Modal, dropdown, panel               |
| `motion-slow`    | `450ms` | Content transition                   |
| `motion-page`    | `600ms` | Tailoring page transition            |

Page transition boleh berada pada rentang **400–700ms**, tetapi default project ditetapkan pada `600ms`.

### Easing

Gunakan easing yang konsisten:

```text
ease-standard
cubic-bezier(0.2, 0.8, 0.2, 1)
```

Untuk elemen keluar:

```text
ease-out
cubic-bezier(0.16, 1, 0.3, 1)
```

Untuk elemen masuk:

```text
ease-enter
cubic-bezier(0.2, 0.8, 0.2, 1)
```

Hindari penggunaan banyak jenis easing yang tidak memiliki fungsi jelas.

### Movement

Gunakan jarak kecil:

```text
distance-xs = 4px
distance-sm = 8px
distance-md = 16px
distance-lg = 24px
```

Sebisa mungkin jangan membuat UI berpindah terlalu jauh.

### Scale

Untuk hover atau active state:

```text
scale-hover = 1.01
scale-press = 0.98
```

Scale harus sangat kecil agar UI tetap terasa profesional.

### Stagger

Untuk beberapa card yang muncul bersamaan:

```text
stagger-sm = 30ms
stagger-md = 50ms
```

Gunakan maksimal sekitar 4–6 elemen sebelum animasi terasa terlalu lambat.

### Motion Rule

Prioritas motion:

```text
Function
   ↓
Feedback
   ↓
Navigation
   ↓
Decoration
```

Animasi fungsional selalu lebih penting daripada animasi dekoratif.

---

# 6. Dashboard Motion

Dashboard tidak boleh penuh animasi.

Gunakan:

* Counter number yang naik saat pertama kali membuka dashboard.
* Progress bar dengan smooth fill.
* Timeline stitching yang berjalan singkat.
* Card muncul dengan stagger animation ringan.
* Reminder muncul dengan fade/slide.

Semua animasi harus berhenti setelah informasi terbaca.

---

# 7. Pattern Library Motion

Pattern Library menjadi salah satu halaman paling visual.

Saat membuka pola:

* Card sedikit expand.
* Pattern image melakukan zoom kecil.
* Detail ukuran muncul seperti lembar pola dibuka.
* Size S/M/L/XL menggunakan transition smooth.
* Stitching line muncul mengikuti perimeter pattern.

---

# 8. Accessibility Rules

Desain harus menargetkan **WCAG 2.2 Level AA** untuk bagian yang relevan.

### 8.1 Reduced Motion

Semua motion non-esensial harus menghormati preferensi pengguna `prefers-reduced-motion`.

Ketika pengguna mengaktifkan reduced motion:

* Page transition gunting/kertas dinonaktifkan.
* Parallax dinonaktifkan.
* Gerakan dekoratif dinonaktifkan.
* Card hover hanya menggunakan perubahan warna/border/shadow.
* Progress menggunakan perubahan state tanpa gerakan panjang.
* Fade singkat masih boleh digunakan bila tidak menimbulkan motion yang berarti.

W3C secara khusus merekomendasikan dukungan terhadap preferensi reduced motion untuk mengurangi efek yang dapat mengganggu atau memicu reaksi pada pengguna tertentu.

Contoh prinsip implementasi:

```css
@media (prefers-reduced-motion: reduce) {
  /* Disable non-essential motion */
}
```

### 8.2 Contrast

Target minimum:

* Text normal: **4.5:1**
* Large text: **3:1**
* UI component dan graphical indicator penting: **3:1**

Rasio tersebut mengikuti kriteria WCAG 2.2 untuk contrast dan non-text contrast.

Warna status tidak boleh menjadi satu-satunya cara untuk menyampaikan informasi.

Contoh:

```text
🟢 Selesai
🟡 Menunggu
🔴 Terlambat
```

Jangan hanya:

```text
Hijau
Kuning
Merah
```

Gunakan kombinasi **warna + icon + text**.

### 8.3 Keyboard Navigation

Semua fungsi utama harus dapat digunakan menggunakan keyboard:

* Sidebar.
* Button.
* Link.
* Form.
* Modal.
* Dropdown.
* Table.
* Search.
* Filter.

Jangan membuat interaksi yang hanya dapat dilakukan dengan mouse.

### 8.4 Focus State

Setiap interactive element harus memiliki visible focus state.

Contoh:

```text
┌───────────────────────┐
│  + Pesanan Baru       │
└───────────────────────┘
       ↑
  visible focus
```

Focus indicator tidak boleh hilang hanya karena desain ingin terlihat minimalis. WCAG 2.2 juga memiliki kriteria khusus mengenai focus appearance dan focus visibility.

### 8.5 Target Size

Interactive target minimal:

```text
24 × 24 CSS px
```

Untuk kenyamanan penggunaan sehari-hari, terutama pada mobile, lebih baik menggunakan target sekitar:

```text
40–48px
```

WCAG 2.2 menetapkan minimum 24×24 CSS px untuk target pointer dengan beberapa pengecualian.

### 8.6 Jangan Mengandalkan Hover

Informasi penting tidak boleh hanya tersedia ketika hover.

Contoh buruk:

```text
Hover → muncul status pesanan
```

Status juga harus tersedia melalui:

* Text.
* Icon.
* Visible state.
* Focus.
* Tap pada mobile.

### 8.7 Animation Safety

Hindari:

* Flashing.
* Rapid blinking.
* Shake berlebihan.
* Zoom ekstrem.
* Gerakan besar secara tiba-tiba.

Konten bergerak otomatis juga harus memiliki mekanisme pause/stop ketika memenuhi kondisi yang relevan. WCAG 2.2 memiliki aturan khusus untuk moving, blinking, scrolling, dan auto-updating content.

### 8.8 Form Accessibility

Semua form harus memiliki:

* Label yang jelas.
* Error message yang menjelaskan masalah.
* Required state yang jelas.
* Focus berpindah secara logis.
* Tidak hanya menggunakan placeholder sebagai label.

Contoh:

```text
Nama Pelanggan *
[________________]

Nomor Telepon *
[________________]

✓ Data berhasil disimpan
```

### 8.9 Responsive Accessibility

Web harus tetap usable pada:

* Desktop.
* Laptop.
* Tablet.
* Mobile browser.

Konten tidak boleh hilang hanya karena viewport kecil.

### 8.10 Decorative Elements

Elemen seperti:

* Gunting.
* Garis jahitan.
* Pattern.
* Kain.
* Benang.

yang hanya berfungsi sebagai dekorasi harus tidak mengganggu screen reader.

Dekorasi tidak boleh menjadi satu-satunya penyampai informasi penting.

---

# 9. UI Style

Gunakan:

* Radius sekitar `10px` sampai `16px`.
* Border tipis.
* Shadow lembut.
* Banyak whitespace.
* Icon rounded dan konsisten.
* Typography modern.
* Background ivory.
* Surface putih.
* Primary dark olive atau charcoal.

UI harus tetap terasa seperti software profesional walaupun memiliki banyak elemen tailoring.

---

# 10. Typography

### Heading

* Manrope
* Plus Jakarta Sans

### Body

* Inter
* DM Sans

Heading dapat menggunakan weight lebih tegas, sedangkan body tetap sederhana dan mudah dibaca.

Pastikan teks tetap dapat diperbesar tanpa kehilangan konten atau fungsi. WCAG 2.2 Level AA mengharuskan teks dapat diperbesar hingga 200% tanpa kehilangan konten atau fungsionalitas, dengan pengecualian tertentu.

---

# 11. Overall Visual Identity

Ketika pertama kali membuka aplikasi, pengguna harus merasakan:

**Professional + Warm + Modern + Tailoring**

Identitas tailoring muncul melalui:

* Stitching line.
* Pattern paper.
* Gunting.
* Benang.
* Kain.
* Meteran.
* Pattern illustration.
* Tailoring motion.

Bukan melalui dekorasi yang memenuhi halaman.

Target akhirnya:

**Aplikasi management profesional yang terasa seperti meja kerja digital seorang penjahit.**

Semua visual, motion, dan interaksi harus tetap mendukung tujuan utama aplikasi:

**membantu penjahit menyelesaikan pekerjaan dengan cepat, jelas, dan nyaman.**
