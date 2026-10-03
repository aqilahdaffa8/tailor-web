<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $pesanan->nomor_pesanan }}</title>
    <style>
        @page { margin: 36px 40px; }
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #29302D; }
        table { width: 100%; border-collapse: collapse; }
        .muted { color: #9A816B; }
        .kecil { font-size: 9px; }
        .kanan { text-align: right; }
        .judul { font-size: 22px; font-weight: bold; color: #29302D; }
        .invoice { font-size: 18px; font-weight: bold; color: #A8754F; letter-spacing: 3px; }
        .kop td { vertical-align: top; padding-bottom: 14px; border-bottom: 2px solid #29302D; }
        .label { font-size: 9px; font-weight: bold; text-transform: uppercase; color: #9A816B; letter-spacing: 1px; }
        .item th { background: #F7F4EE; border-top: 1px solid #E7DED2; border-bottom: 1px solid #E7DED2; padding: 7px 8px; font-size: 9px; text-transform: uppercase; text-align: left; color: #38443D; }
        .item td { padding: 8px; border-bottom: 1px solid #E7DED2; vertical-align: top; }
        .total td { padding: 4px 0; }
        .total .garis td { border-top: 2px solid #29302D; padding-top: 7px; }
        .cap { display: inline-block; border: 2px solid #9A816B; padding: 4px 14px; font-size: 13px; font-weight: bold; letter-spacing: 2px; text-transform: uppercase; color: #9A816B; }
        .cap-lunas { border-color: #6F8A78; color: #6F8A78; }
        .cap-dp { border-color: #C49A52; color: #A8754F; }
        .cap-belum { border-color: #B86B67; color: #B86B67; }
        .bayar td { padding: 5px 6px 5px 0; border-bottom: 1px solid #E7DED2; font-size: 10px; }
        .kaki { margin-top: 30px; padding-top: 12px; border-top: 1px dashed #9A816B; text-align: center; }
    </style>
</head>
<body>
    @php
        $rupiah = fn ($angka) => 'Rp '.number_format((float) $angka, 0, ',', '.');
        $metode = ['tunai' => 'Tunai', 'transfer' => 'Transfer', 'qris' => 'QRIS'];
        $kelasCap = ['Lunas' => 'cap-lunas', 'DP' => 'cap-dp', 'Belum Bayar' => 'cap-belum'][$ringkasan['status']] ?? '';
    @endphp

    <table class="kop">
        <tr>
            <td style="width: 55%;">
                <div class="judul">{{ $usaha['nama_usaha'] ?? 'Tailor' }}</div>
                <div class="muted kecil" style="margin-top: 5px; line-height: 1.5;">
                    @if(! empty($usaha['alamat']) && $usaha['alamat'] !== '-'){{ $usaha['alamat'] }}<br>@endif
                    @if(! empty($usaha['no_hp']) && $usaha['no_hp'] !== '-')Telp/WA: {{ $usaha['no_hp'] }}@endif
                </div>
            </td>
            <td class="kanan">
                <div class="invoice">INVOICE</div>
                <div style="font-weight: bold; margin-top: 4px;">{{ $pesanan->nomor_pesanan }}</div>
                <div class="muted kecil" style="margin-top: 3px;">Tanggal pesan: {{ $pesanan->tanggal_pesan->format('d M Y') }}</div>
                <div class="muted kecil">Deadline: {{ $pesanan->deadline->format('d M Y') }}</div>
            </td>
        </tr>
    </table>

    <table style="margin: 18px 0;">
        <tr>
            <td style="width: 60%; vertical-align: top;">
                <div class="label">Ditagihkan kepada</div>
                <div style="font-size: 14px; font-weight: bold; margin-top: 4px;">{{ $pesanan->pelanggan->nama }}</div>
                <div style="margin-top: 2px;">{{ $pesanan->pelanggan->no_hp }}</div>
                @if($pesanan->pelanggan->alamat)
                    <div class="muted kecil" style="margin-top: 2px;">{{ $pesanan->pelanggan->alamat }}</div>
                @endif
            </td>
            <td class="kanan" style="vertical-align: top;">
                <span class="cap {{ $kelasCap }}">{{ $ringkasan['status'] }}</span>
            </td>
        </tr>
    </table>

    <table class="item">
        <thead>
            <tr>
                <th style="width: 28px;">No</th>
                <th>Rincian Pekerjaan</th>
                <th class="kanan" style="text-align: right; width: 110px;">Harga</th>
            </tr>
        </thead>
        <tbody>
            @foreach($pesanan->itemPesanan as $item)
                <tr>
                    <td class="muted">{{ $loop->iteration }}</td>
                    <td>
                        <strong>{{ $item->jenisJasa->nama_jasa ?? 'Jasa' }}</strong><br>
                        <span class="muted kecil">
                            {{ $item->detail_pekerjaan ?: '-' }}
                            @if($item->polaDetail) &middot; Pola {{ $item->polaDetail->jenisPakaian->nama_jenis ?? '' }} {{ $item->polaDetail->size }} @endif
                        </span>
                    </td>
                    <td class="kanan">{{ $rupiah($item->harga) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table style="margin-top: 14px;">
        <tr>
            <td style="width: 50%;"></td>
            <td>
                <table class="total">
                    <tr>
                        <td class="muted">Total tagihan</td>
                        <td class="kanan"><strong>{{ $rupiah($ringkasan['total_tagihan']) }}</strong></td>
                    </tr>
                    <tr>
                        <td class="muted">Sudah dibayar</td>
                        <td class="kanan" style="color: #6F8A78;">{{ $rupiah($ringkasan['total_dibayar']) }}</td>
                    </tr>
                    <tr class="garis">
                        <td><strong>Sisa pembayaran</strong></td>
                        <td class="kanan" style="color: #A8754F;"><strong>{{ $rupiah($ringkasan['sisa']) }}</strong></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    @if($pesanan->pembayaran->isNotEmpty())
        <div class="label" style="margin: 22px 0 6px;">Riwayat Pembayaran</div>
        <table class="bayar">
            @foreach($pesanan->pembayaran as $bayar)
                <tr>
                    <td style="width: 90px;">{{ $bayar->tanggal_bayar->format('d M Y') }}</td>
                    <td style="width: 70px;">{{ $metode[$bayar->metode] ?? ucfirst($bayar->metode) }}</td>
                    <td class="muted">{{ $bayar->catatan }}</td>
                    <td class="kanan">{{ $rupiah($bayar->jumlah) }}</td>
                </tr>
            @endforeach
        </table>
    @endif

    @if($pesanan->catatan)
        <p style="margin-top: 16px;"><strong>Catatan:</strong> {{ $pesanan->catatan }}</p>
    @endif

    <div class="kaki">
        @if(! empty($usaha['catatan_kaki_invoice']))
            <div>{{ $usaha['catatan_kaki_invoice'] }}</div>
        @endif
        <div class="muted kecil" style="margin-top: 6px;">Dicetak pada {{ $dicetak_pada->format('d M Y, H:i') }}</div>
    </div>
</body>
</html>
