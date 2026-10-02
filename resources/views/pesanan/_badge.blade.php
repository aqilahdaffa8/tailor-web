@php
    $daftarWarna = [
        // Status pesanan
        'aktif' => ['Aktif', 'bg-[#C49A52]/15 text-[#A8754F]'],
        'selesai' => ['Selesai', 'bg-[#6F8A78]/15 text-[#6F8A78]'],
        'diambil' => ['Diambil', 'bg-[#38443D]/10 text-[#38443D]'],
        'dibatalkan' => ['Dibatalkan', 'bg-[#B86B67]/15 text-[#B86B67]'],
        // Status item
        'menunggu' => ['Menunggu', 'bg-[#E7DED2]/70 text-[#9A816B]'],
        'diproses' => ['Diproses', 'bg-[#C49A52]/15 text-[#A8754F]'],
        // Status pembayaran
        'Belum Bayar' => ['Belum Bayar', 'bg-[#B86B67]/15 text-[#B86B67]'],
        'DP' => ['DP', 'bg-[#C49A52]/15 text-[#A8754F]'],
        'Lunas' => ['Lunas', 'bg-[#6F8A78]/15 text-[#6F8A78]'],
    ];
    [$label, $kelas] = $daftarWarna[$status] ?? [ucfirst((string) $status), 'bg-[#F7F4EE] text-[#9A816B]'];
@endphp
<span class="inline-flex items-center px-2.5 py-1 rounded-full text-[11px] font-semibold {{ $kelas }}">{{ $label }}</span>
