<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $pesanan->nomor_pesanan }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff !important; }
            .kertas { box-shadow: none !important; border: 0 !important; margin: 0 !important; max-width: none !important; }
        }
        @page { size: A4; margin: 14mm; }
    </style>
</head>
<body class="bg-zinc-50 text-zinc-900 font-sans antialiased">
    @php
        $rupiah = fn ($angka) => 'Rp '.number_format((float) $angka, 0, ',', '.');
        $metode = ['tunai' => 'Tunai', 'transfer' => 'Transfer', 'qris' => 'QRIS'];
        $warnaStatus = [
            'Lunas' => 'border-emerald-600 text-emerald-700 bg-emerald-50',
            'DP' => 'border-amber-500 text-amber-700 bg-amber-50',
            'Belum Bayar' => 'border-rose-500 text-rose-700 bg-rose-50',
        ];
    @endphp

    <div class="no-print max-w-3xl mx-auto px-4 pt-6 flex items-center justify-between gap-3">
        <a href="{{ route('pesanan.show', $pesanan) }}" class="text-sm font-medium text-zinc-500 hover:text-zinc-900 transition-colors">&larr; Kembali ke Pesanan</a>
        <div class="flex items-center gap-2">
            <a href="{{ route('pesanan.invoice.pdf', $pesanan) }}"
               class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg border border-zinc-200 bg-white text-xs font-medium text-zinc-700 hover:bg-zinc-50 transition-colors shadow-xs">
                <svg class="w-4 h-4 text-zinc-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Unduh PDF
            </a>
            <button type="button" onclick="window.print()"
                    class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-zinc-900 hover:bg-zinc-800 text-white text-xs font-medium transition-colors shadow-xs cursor-pointer">
                <svg class="w-4 h-4 text-zinc-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                </svg>
                Cetak
            </button>
        </div>
    </div>

    <main class="kertas max-w-3xl mx-auto my-6 bg-white rounded-2xl border border-zinc-200 shadow-sm p-8 sm:p-12">
        {{-- Kop --}}
        <header class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6 pb-6 border-b-2 border-zinc-900">
            <div>
                <h1 class="text-2xl font-bold text-zinc-900 tracking-tight">{{ $usaha['nama_usaha'] ?? 'Tailor' }}</h1>
                <p class="text-xs text-zinc-500 mt-1.5 leading-relaxed">
                    @if(! empty($usaha['alamat']) && $usaha['alamat'] !== '-') {{ $usaha['alamat'] }}<br> @endif
                    @if(! empty($usaha['no_hp']) && $usaha['no_hp'] !== '-') Telp/WA: {{ $usaha['no_hp'] }} @endif
                </p>
            </div>
            <div class="sm:text-right">
                <p class="text-xl font-bold tracking-widest text-zinc-900">INVOICE</p>
                <p class="text-sm font-semibold text-zinc-800 mt-1 tabular-nums">{{ $pesanan->nomor_pesanan }}</p>
                <p class="text-xs text-zinc-500 mt-0.5 tabular-nums">Tanggal pesan: {{ $pesanan->tanggal_pesan->format('d M Y') }}</p>
                <p class="text-xs text-zinc-500 tabular-nums">Deadline: {{ $pesanan->deadline->format('d M Y') }}</p>
            </div>
        </header>

        {{-- Pelanggan & status --}}
        <section class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 py-6">
            <div>
                <p class="text-xs font-medium text-zinc-400 uppercase tracking-wider">Ditagihkan kepada</p>
                <p class="font-bold text-lg text-zinc-900 mt-1">{{ $pesanan->pelanggan->nama }}</p>
                <p class="text-xs text-zinc-600 mt-0.5 tabular-nums">{{ $pesanan->pelanggan->no_hp }}</p>
                @if($pesanan->pelanggan->alamat)
                    <p class="text-xs text-zinc-500 mt-1 max-w-xs">{{ $pesanan->pelanggan->alamat }}</p>
                @endif
            </div>
            <div class="inline-block self-start border-2 rounded-lg px-4 py-1.5 font-bold tracking-widest uppercase text-xs {{ $warnaStatus[$ringkasan['status']] ?? 'border-zinc-300 text-zinc-700 bg-zinc-100' }}">
                {{ $ringkasan['status'] }}
            </div>
        </section>

        {{-- Rincian item --}}
        <table class="w-full text-sm">
            <thead>
                <tr class="border-y border-zinc-200 bg-zinc-50">
                    <th class="text-left py-2.5 px-3 text-xs font-semibold text-zinc-700 uppercase tracking-wider w-10">No</th>
                    <th class="text-left py-2.5 px-3 text-xs font-semibold text-zinc-700 uppercase tracking-wider">Rincian Pekerjaan</th>
                    <th class="text-right py-2.5 px-3 text-xs font-semibold text-zinc-700 uppercase tracking-wider">Harga</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-200">
                @foreach($pesanan->itemPesanan as $item)
                    <tr>
                        <td class="py-3 px-3 text-zinc-400 align-top tabular-nums">{{ $loop->iteration }}</td>
                        <td class="py-3 px-3 align-top">
                            <p class="font-medium text-zinc-900">{{ $item->jenisJasa->nama_jasa ?? 'Jasa' }}</p>
                            <p class="text-xs text-zinc-500 mt-0.5">
                                {{ $item->detail_pekerjaan ?: '-' }}
                                @if($item->polaDetail) &middot; Pola {{ $item->polaDetail->jenisPakaian->nama_jenis ?? '' }} {{ $item->polaDetail->size }} @endif
                            </p>
                        </td>
                        <td class="py-3 px-3 text-right align-top whitespace-nowrap font-medium text-zinc-900 tabular-nums">{{ $rupiah($item->harga) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Total --}}
        <div class="flex justify-end mt-4">
            <div class="w-full sm:w-72 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-zinc-500">Total tagihan</span>
                    <span class="font-semibold text-zinc-900 tabular-nums">{{ $rupiah($ringkasan['total_tagihan']) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-zinc-500">Sudah dibayar</span>
                    <span class="font-medium text-emerald-600 tabular-nums">{{ $rupiah($ringkasan['total_dibayar']) }}</span>
                </div>
                <div class="flex justify-between border-t-2 border-zinc-900 pt-2">
                    <span class="font-semibold text-zinc-900">Sisa pembayaran</span>
                    <span class="font-bold text-zinc-900 tabular-nums">{{ $rupiah($ringkasan['sisa']) }}</span>
                </div>
            </div>
        </div>

        {{-- Riwayat pembayaran --}}
        @if($pesanan->pembayaran->isNotEmpty())
            <section class="mt-8 pt-6 border-t border-zinc-200">
                <h2 class="text-xs font-semibold text-zinc-500 uppercase tracking-wider mb-2">Riwayat Pembayaran</h2>
                <table class="w-full text-xs">
                    <tbody class="divide-y divide-zinc-100">
                        @foreach($pesanan->pembayaran as $bayar)
                            <tr>
                                <td class="py-2 pr-3 text-zinc-700 tabular-nums">{{ $bayar->tanggal_bayar->format('d M Y') }}</td>
                                <td class="py-2 pr-3 text-zinc-700">{{ $metode[$bayar->metode] ?? ucfirst($bayar->metode) }}</td>
                                <td class="py-2 pr-3 text-zinc-500">{{ $bayar->catatan }}</td>
                                <td class="py-2 text-right font-medium text-zinc-900 tabular-nums">{{ $rupiah($bayar->jumlah) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endif

        @if($pesanan->catatan)
            <p class="mt-6 text-xs text-zinc-600"><span class="font-semibold text-zinc-800">Catatan:</span> {{ $pesanan->catatan }}</p>
        @endif

        @if(! empty($usaha['catatan_kaki_invoice']))
            <div class="mt-8 pt-6 border-t border-zinc-200 text-center text-xs text-zinc-500">
                {{ $usaha['catatan_kaki_invoice'] }}
            </div>
        @endif
    </main>
</body>
</html>
