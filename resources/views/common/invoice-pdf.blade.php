<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Invoice - {{ $invoice->invoice_number }}</title>
    <style>
        @page {
            margin: 0;
        }
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 10px;
            color: #334155;
            line-height: 1.6;
            margin: 0;
            padding: 0;
            background-color: #ffffff;
        }
        
        /* Corporate Color Palette */
        :root {
            --brand-main: #002e5d; /* Deep Royal Blue */
            --brand-accent: #0ea5e9; /* Sky Blue */
            --text-dark: #0f172a;
            --text-muted: #64748b;
            --border-light: #f1f5f9;
        }

        .side-accent {
            position: absolute;
            left: 0;
            top: 0;
            bottom: 0;
            width: 8px;
            background-color: #002e5d;
        }

        .container {
            padding: 30px 40px;
        }

        .header-table {
            width: 100%;
            margin-bottom: 25px;
        }
        .logo {
            width: 80px;
            height: auto;
        }
        .company-name {
            font-size: 20px;
            font-weight: 900;
            color: #002e5d;
            margin: 0;
            letter-spacing: -0.5px;
            text-transform: uppercase;
        }
        .company-meta {
            font-size: 9px;
            color: #64748b;
            margin-top: 4px;
        }

        .invoice-title-block {
            text-align: right;
        }
        .invoice-title {
            font-size: 32px;
            font-weight: 100;
            color: #e2e8f0;
            margin: 0;
            letter-spacing: 4px;
            text-transform: uppercase;
        }
        .invoice-number {
            font-size: 14px;
            font-weight: bold;
            color: #0f172a;
            margin-top: 5px;
        }

        .status-box {
            display: inline-block;
            padding: 6px 14px;
            border-radius: 6px;
            font-weight: 800;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 15px;
        }
        .status-paid { background-color: #f0fdf4; color: #15803d; border: 1px solid #bbf7d0; }
        .status-unpaid { background-color: #fef2f2; color: #b91c1c; border: 1px solid #fecaca; }
        .status-overdue { background-color: #fffaf0; color: #c2410c; border: 1px solid #ffedd5; }

        .meta-grid {
            width: 100%;
            margin-bottom: 25px;
            border-collapse: collapse;
        }
        .meta-grid td {
            vertical-align: top;
            width: 33.33%;
        }
        .meta-label {
            font-size: 8px;
            font-weight: bold;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 8px;
        }
        .meta-value {
            font-size: 11px;
            color: #1e293b;
        }
        .meta-value strong {
            color: #002e5d;
        }

        .item-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .item-table th {
            text-align: left;
            padding: 12px 15px;
            background-color: #f8fafc;
            color: #64748b;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-bottom: 2px solid #002e5d;
        }
        .item-table td {
            padding: 11px 15px;
            border-bottom: 1px solid #f1f5f9;
        }
        .item-description {
            font-size: 12px;
            font-weight: bold;
            color: #0f172a;
        }
        .item-subtext {
            font-size: 9px;
            color: #94a3b8;
            margin-top: 4px;
        }

        .summary-container {
            float: right;
            width: 280px;
        }
        .summary-row {
            padding: 8px 15px;
            border-bottom: 1px solid #f1f5f9;
        }
        .summary-label {
            float: left;
            color: #64748b;
            font-size: 10px;
        }
        .summary-value {
            float: right;
            font-weight: bold;
            color: #1e293b;
            font-size: 10px;
        }
        .grand-total-row {
            background-color: #002e5d;
        }
        .grand-total-row td {
            padding: 15px !important;
            border: none !important;
            color: #ffffff !important;
            vertical-align: middle;
        }
        .grand-total-label {
            font-weight: 800;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .grand-total-value {
            font-weight: 900;
            font-size: 18px;
            text-align: right;
        }
        
        .payment-box {
            margin-top: 30px;
            padding: 20px;
            background-color: #f8fafc;
            border-radius: 12px;
            border: 1px solid #e2e8f0;
            width: 320px;
        }
        .payment-title {
            font-weight: 900;
            font-size: 9px;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            color: #002e5d;
            margin-bottom: 12px;
            border-bottom: 1px solid #cbd5e1;
            padding-bottom: 8px;
        }
        .payment-info {
            font-size: 10px;
            color: #475569;
            line-height: 1.8;
        }

        .signature-box {
            float: right;
            text-align: center;
            width: 180px;
            margin-top: 30px;
        }
        .signature-title {
            font-size: 8px;
            font-weight: bold;
            text-transform: uppercase;
            color: #94a3b8;
            margin-bottom: 35px;
        }
        .signature-line {
            border-bottom: 1px solid #002e5d;
            margin-bottom: 6px;
        }
        .signature-dept {
            font-size: 10px;
            font-weight: 900;
            color: #002e5d;
        }

        .footer {
            position: absolute;
            bottom: 40px;
            left: 60px;
            right: 60px;
            text-align: center;
            border-top: 1px solid #f1f5f9;
            padding-top: 20px;
            font-size: 8px;
            color: #94a3b8;
            letter-spacing: 1px;
            text-transform: uppercase;
        }
        .clear { clear: both; }
    </style>
</head>
<body>
    <div class="side-accent"></div>
    
    <div class="container">
        <!-- Header -->
        <table class="header-table">
            <tr>
                <td width="50%">
                    @php
                        $logoPath = public_path('images/logo/djawa_net_biru.png');
                        $logoExists = file_exists($logoPath);
                    @endphp
                    @if($logoExists)
                        <img src="data:image/png;base64,{{ base64_encode(file_get_contents($logoPath)) }}" class="logo">
                    @endif
                    <h1 class="company-name">PT DJAVVA NETWORK SOLUTION</h1>
                    <div class="company-meta">
                        Perumahan Gramapuri Persada B02 25, Sukajaya Cibitung Jawa Barat<br>
                        Support: 0822-4646-8335 | www.djavva.net
                    </div>
                </td>
                <td width="50%" class="invoice-title-block">
                    <p class="invoice-title">Invoice</p>
                    <p class="invoice-number">#{{ $invoice->invoice_number }}</p>
                    <div class="status-box status-{{ $invoice->status }}">
                        {{ in_array($invoice->status, ['paid', 'paid_transfer', 'paid_cash', 'paid_gateway']) ? 'Settled / Lunas' : ($invoice->status === 'overdue' ? 'Overdue' : 'Awaiting Payment') }}
                    </div>
                </td>
            </tr>
        </table>

        <!-- Meta Data Grid -->
        <table class="meta-grid">
            <tr>
                <td>
                    <div class="meta-label">Ditujukan Kepada</div>
                    <div class="meta-value">
                        <strong>{{ strtoupper($invoice->customer->full_name) }}</strong><br>
                        ID Record: {{ $invoice->customer->customer_code }}<br>
                        {{ $invoice->customer->address }}
                    </div>
                </td>
                <td>
                    <div class="meta-label">Periode Layanan</div>
                    <div class="meta-value">
                        <strong>{{ \Carbon\Carbon::parse($invoice->period_start)->translatedFormat('F Y') }}</strong><br>
                        <span style="font-weight: 700; color: #1e293b;">Paket Internet:</span> {{ $invoice->customer->package->name ?? '-' }}
                    </div>
                </td>
                <td>
                    <div class="meta-label">Status Keuangan</div>
                    <div class="meta-value">
                        Terbit: {{ \Carbon\Carbon::parse($invoice->created_at)->translatedFormat('d M Y') }}<br>
                        <span style="color: #ef4444; font-weight: bold;">Jatuh Tempo: {{ \Carbon\Carbon::parse($invoice->due_date)->translatedFormat('d M Y') }}</span>
                    </div>
                </td>
            </tr>
        </table>

        <!-- Items Table -->
        <table class="item-table">
            <thead>
                <tr>
                    <th width="70%">Deskripsi Layanan</th>
                    <th class="text-right">Total (IDR)</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div class="item-description">Langganan Internet — {{ $invoice->customer->package->name }}</div>
                        <div class="item-subtext">Akses data unlimited melalui infrastruktur fiber optic.</div>
                    </td>
                    <td class="text-right">Rp{{ number_format($invoice->amount, 0, ',', '.') }}</td>
                </tr>
                @if($invoice->installation_fee > 0)
                <tr>
                    <td>
                        <div class="item-description">Biaya Instalasi & Pemasangan</div>
                        <div class="item-subtext">Biaya satu kali untuk setting node dan aktivasi terminal.</div>
                    </td>
                    <td class="text-right">Rp{{ number_format($invoice->installation_fee, 0, ',', '.') }}</td>
                </tr>
                @endif
                <tr>
                    <td>
                        <div class="item-description" style="color: #64748b; font-weight: normal;">Total Tagihan</div>
                    </td>
                    <td class="text-right" style="color: #64748b;">Rp{{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="clear"></div>

        <!-- Summary & Totals -->
        <div class="summary-container">
            <table class="summary-table">
                <tr>
                    <td class="summary-label">Subtotal Tagihan</td>
                    <td class="summary-value text-right">Rp{{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                </tr>
                <tr class="grand-total-row">
                    <td class="grand-total-label">TOTAL PEMBAYARAN</td>
                    <td class="grand-total-value">Rp{{ number_format($invoice->total_amount, 0, ',', '.') }}</td>
                </tr>
            </table>
        </div>

        <!-- Payment Info -->
        <div class="payment-box">
            @if(in_array($invoice->status, ['paid', 'paid_transfer', 'paid_cash', 'paid_gateway']))
                <div class="payment-title" style="color: #15803d; border-color: #bbf7d0;">Pembayaran Dikonfirmasi</div>
                <div class="payment-info" style="color: #166534;">
                    Lunas & Terbayar via **Xendit Payment Gateway**.<br>
                    <span style="font-size: 8px; margin-top: 5px; display: block;">ID Transaksi Otomatis: #{{ $invoice->id }}</span>
                </div>
            @elseif($invoice->status === 'overdue' || $invoice->status === 'unpaid')
                <div class="payment-title">Metode Pembayaran</div>
                <div class="payment-info">
                    Metode Pembayaran: **Xendit Payment Gateway**.<br>
                    Virtual Account, QRIS, & Kartu Kredit.<br>
                    Silakan lakukan pembayaran melalui **Portal Pelanggan**.
                </div>
            @else
                <div class="payment-title">Administrative Record</div>
                <div class="payment-info">
                    Status: **{{ strtoupper($invoice->status) }}**
                </div>
            @endif
        </div>

        <!-- Signature -->
        <div class="signature-box">
            <div class="signature-title">Catatan Digital Resmi</div>
            <div class="signature-space"></div>
            <div class="signature-line"></div>
            <div class="signature-dept">BAGIAN KEUANGAN</div>
        </div>

        <div class="clear"></div>

        <!-- Footer -->
        <div class="footer">
            DOKUMEN RESMI YANG DITERBITKAN OLEH SISTEM PT DJAVVA NETWORK SOLUTION<br>
            &copy; {{ date('Y') }} PT DJAVVA NETWORK SOLUTION. HAK CIPTA DILINDUNGI.
        </div>
    </div>
</body>
</html>
