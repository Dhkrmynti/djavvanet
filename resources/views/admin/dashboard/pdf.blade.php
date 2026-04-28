Laporan Pendapatan {{ $startDate }} s/d {{ $endDate }}
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 13px; color: #333; margin: 0; padding: 20px; }
        .header { border-bottom: 2px solid #f97316; padding-bottom: 10px; margin-bottom: 20px; text-align: center; }
        .header h1 { font-size: 20px; margin: 0; color: #f97316; }
        .header p { margin: 5px 0 0 0; font-size: 12px; color: #666; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px 10px; text-align: left; }
        th { background-color: #f1f5f9; font-weight: bold; color: #1e293b; font-size: 12px; text-transform: uppercase; }
        .text-right { text-align: right; }
        .text-center { text-align: center; }
        .font-bold { font-weight: bold; }
        .footer-total { margin-top: 20px; text-align: right; font-size: 16px; font-weight: bold; color: #f97316;}
        .printed-at { font-size: 10px; color: #999; text-align: center; margin-top: 40px; }
{{ $companyName }} - Rekapitulasi Tagihan & Laporan Finansial
Periode Waktu: {{ \Carbon\Carbon::parse($startDate)->translatedFormat('d F Y') }} sampai dengan {{ \Carbon\Carbon::parse($endDate)->translatedFormat('d F Y') }}
No
Tgl Terbit
No Invoice
Pelanggan
Paket
Total Tagihan (Rp)
            @forelse($invoices as $index => $inv)
{{ $index + 1 }}
{{ $inv->created_at->translatedFormat('d/m/y H:i') }}
{{ $inv->invoice_number }}
{{ $inv->customer->name ?? '-' }} ({{ $inv->customer->customer_code ?? '' }})
{{ $inv->customer->package->name ?? '-' }}
{{ number_format($inv->total_amount, 0, ',', '.') }}
            @empty
Tidak ada transaksi tagihan LUNAS pada periode ini.
            @endforelse
        TOTAL RIWAYAT PENDAPATAN: Rp {{ number_format($totalRevenue, 0, ',', '.') }}
        Dokumen ini di-generate pada {{ now()->translatedFormat('d F Y, H:i:s') }} lewat Sistem PT Djavva Network Solution
