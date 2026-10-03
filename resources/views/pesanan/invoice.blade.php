<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Invoice {{ $pesanan->nomor_pesanan }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
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
<body class="bg-[#F7F4EE] text-[#29302D] font-sans antialiased">
    @php
        $rupiah = fn ($angka) => 'Rp '.number_format((float) $angka, 0, ',', '.');
        $metode = ['tunai' => 'Tunai', 'transfer' => 'Transfer', 'qris' => 'QRIS'];
        $warnaStatus = [
            'Lunas' => 'border-[#6F8A78] text-[#6F8A78]',
            'DP' => 'border-[#C49A52] text-[#A8754F]',
            'Belum Bayar' => 'border-[#B86B67] text-[#B86B67]',
        ];
    @endphp

    <div class="no-print max-w-3xl mx-auto px-4 pt-6 flex items-center justify-between gap-3">
        <a href="{{ route('pesanan.show', $pesanan) }}" class="text-sm text-[#9A816B] hover:text-[#29302D] transition-colors">&larr; Kembali ke Pesanan</a>
        <div class="flex items-center gap-2">
        <a href="{{ route('pesanan.invoice.pdf', $pesanan) }}"
           class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl border border-[#E7DED2] bg-white text-xs font-semibold text-[#38443D] hover:bg-[#F7F4EE] transition-colors">
            <svg class="w-4 h-4 text-[#A8754F]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Unduh PDF
        </a>
        <button type="button" onclick="window.print()"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-[#29302D] hover:bg-[#38443D] text-white text-xs font-semibold transition-all cursor-pointer">
            <svg class="w-4 h-4 text-[#C49A52]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Cetak
        </button>
        </div>
    </div>

    <main class="kertas max-w-3xl mx-auto my-6 bg-white rounded-2xl border border-[#E7DED2] shadow-sm p-8 sm:p-12">
        {{-- Kop --}}
        <header class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-6 pb-6 border-b-2 border-[#29302D]">
            <div>
                <h1 class="font-heading text-2xl font-bold text-[#29302D]">{{ $usaha['nama_usaha'] ?? 'Tailor' }}</h1>
                <p class="text-xs text-[#9A816B] mt-1.5 leading-relaxed">
                    @if(! empty($usaha['alamat']) && $usaha['alamat'] !== '-') {{ $usaha['alamat'] }}<br> @endif
                    @if(! empty($usaha['no_hp']) && $usaha['no_hp'] !== '-') Telp/WA: {{ $usaha['no_hp'] }} @endif
                </p>
            </div>
            <div class="sm:text-right">
                <p class="font-heading text-xl font-bold tracking-widest text-[#A8754F]">INVOICE</p>
                <p class="text-sm font-semibold mt-1">{{ $pesanan->nomor_pesanan }}</p>
                <p class="text-[11px] text-[#9A816B] mt-0.5">Tanggal pesan: {{ $pesanan->tanggal_pesan->format('d M Y') }}</p>
                <p class="text-[11px] text-[#9A816B]">Deadline: {{ $pesanan->deadline->format('d M Y') }}</p>
            </div>
        </header>

        {{-- Pelanggan & status --}}
        <section class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-4 py-6">
            <div>
                <p class="text-[11px] font-semibold text-[#9A816B] uppercase tracking-wider">Ditagihkan kepada</p>
                <p class="font-heading font-semibold text-lg mt-1">{{ $pesanan->pelanggan->nama }}</p>
                <p class="text-xs text-[#38443D]">{{ $pesanan->pelanggan->no_hp }}</p>
                @if($pesanan->pelanggan->alamat)
                    <p class="text-xs text-[#9A816B] max-w-xs">{{ $pesanan->pelanggan->alamat }}</p>
                @endif
            </div>
            <div class="inline-block self-start rotate-[-4deg] border-2 rounded-lg px-4 py-1.5 font-heading font-bold tracking-widest uppercase text-sm {{ $warnaStatus[$ringkasan['status']] ?? 'border-[#9A816B] text-[#9A816B]' }}">
                {{ $ringkasan['status'] }}
            </div>
        </section>

        {{-- Rincian item --}}
        <table class="w-full text-sm">
            <thead>
                <tr class="border-y border-[#E7DED2] bg-[#F7F4EE]/70">
                    <th class="text-left py-2.5 px-3 text-[11px] font-semibold text-[#38443D] uppercase tracking-wider w-10">No</th>
                    <th class="text-left py-2.5 px-3 text-[11px] font-semibold text-[#38443D] uppercase tracking-wider">Rincian Pekerjaan</th>
                    <th class="text-right py-2.5 px-3 text-[11px] font-semibold text-[#38443D] uppercase tracking-wider">Harga</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-[#E7DED2]">
                @foreach($pesanan->itemPesanan as $item)
                    <tr>
                        <td class="py-3 px-3 text-[#9A816B] align-top">{{ $loop->iteration }}</td>
                        <td class="py-3 px-3 align-top">
                            <p class="font-medium">{{ $item->jenisJasa->nama_jasa ?? 'Jasa' }}</p>
                            <p class="text-xs text-[#9A816B]">
                                {{ $item->detail_pekerjaan ?: '-' }}
                                @if($item->polaDetail) &middot; Pola {{ $item->polaDetail->jenisPakaian->nama_jenis ?? '' }} {{ $item->polaDetail->size }} @endif
                            </p>
                        </td>
                        <td class="py-3 px-3 text-right align-top whitespace-nowrap">{{ $rupiah($item->harga) }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Total --}}
        <div class="flex justify-end mt-4">
            <div class="w-full sm:w-72 space-y-2 text-sm">
                <div class="flex justify-between">
                    <span class="text-[#9A816B]">Total tagihan</span>
                    <span class="font-heading font-bold">{{ $rupiah($ringkasan['total_tagihan']) }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-[#9A816B]">Sudah dibayar</span>
                    <span class="font-medium text-[#6F8A78]">{{ $rupiah($ringkasan['total_dibayar']) }}</span>
                </div>
                <div class="flex justify-between border-t-2 border-[#29302D] pt-2">
                    <span class="font-semibold">Sisa pembayaran</span>
                    <span class="font-heading font-bold text-[#A8754F]">{{ $rupiah($ringkasan['sisa']) }}</span>
                </div>
            </div>
        </div>

        {{-- Riwayat pembayaran --}}
        @if($pesanan->pembayaran->isNotEmpty())
            <section class="mt-8">
                <h2 class="text-[11px] font-semibold text-[#9A816B] uppercase tracking-wider mb-2">Riwayat Pembayaran</h2>
                <table class="w-full text-xs">
                    <tbody class="divide-y divide-[#E7DED2]">
                        @foreach($pesanan->pembayaran as $bayar)
                            <tr>
                                <td class="py-2 pr-3 text-[#38443D]">{{ $bayar->tanggal_bayar->format('d M Y') }}</td>
                                <td class="py-2 pr-3 text-[#38443D]">{{ $metode[$bayar->metode] ?? ucfirst($bayar->metode) }}</td>
                                <td class="py-2 pr-3 text-[#9A816B]">{{ $bayar->catatan }}</td>
                                <td class="py-2 text-right font-medium">{{ $rupiah($bayar->jumlah) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </section>
        @endif

        @if($pesanan->catatan)
            <p class="mt-6 text-xs text-[#38443D]"><span class="font-semibold">Catatan:</span> {{ $pesanan->catatan }}</p>
        @endif

        <footer class="mt-10 pt-5 border-t border-dashed border-[#9A816B]/50 text-center">
            @if(! empty($usaha['catatan_kaki_invoice']))
                <p class="text-xs text-[#38443D]">{{ $usaha['catatan_kaki_invoice'] }}</p>
            @endif
            <p class="text-[10px] text-[#9A816B] mt-2">Dicetak pada {{ $dicetak_pada->format('d M Y, H:i') }}</p>
        </footer>
    </main>
</body>
</html>
