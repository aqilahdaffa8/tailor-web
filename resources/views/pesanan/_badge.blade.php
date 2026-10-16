@php
    $daftarWarna = [
        // Status pesanan
        'aktif' => ['Aktif', 'bg-blue-50 text-blue-700 ring-1 ring-blue-600/20'],
        'selesai' => ['Selesai', 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20'],
        'diambil' => ['Diambil', 'bg-zinc-100 text-zinc-700 ring-1 ring-zinc-600/20'],
        'dibatalkan' => ['Dibatalkan', 'bg-rose-50 text-rose-700 ring-1 ring-rose-600/20'],
        // Status item
        'menunggu' => ['Menunggu', 'bg-zinc-100 text-zinc-600 ring-1 ring-zinc-500/20'],
        'diproses' => ['Diproses', 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20'],
        // Status pembayaran
        'Belum Bayar' => ['Belum Bayar', 'bg-rose-50 text-rose-700 ring-1 ring-rose-600/20'],
        'DP' => ['DP', 'bg-amber-50 text-amber-700 ring-1 ring-amber-600/20'],
        'Lunas' => ['Lunas', 'bg-emerald-50 text-emerald-700 ring-1 ring-emerald-600/20'],
    ];
    [$label, $kelas] = $daftarWarna[$status] ?? [ucfirst((string) $status), 'bg-zinc-100 text-zinc-700 ring-1 ring-zinc-600/20'];
@endphp
<span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $kelas }}">{{ $label }}</span>
