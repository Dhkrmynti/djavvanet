<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Layanan Terisolir - DJAVVA NETWORK</title>
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@400;600;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-color: #0a0a0c;
            --card-bg: #141418;
            --primary: #f87171; /* Soft Red for Alert */
            --accent: #3b82f6; /* Blue for action */
            --text-main: #e2e8f0;
            --text-dim: #94a3b8;
            --radius: 16px;
            --border: rgba(255, 255, 255, 0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Outfit', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            overflow: hidden;
            padding: 20px;
        }

        /* Abstract Industrial background */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 20%, rgba(248, 113, 113, 0.05) 0%, transparent 40%),
                radial-gradient(circle at 80% 80%, rgba(59, 130, 246, 0.05) 0%, transparent 40%);
            z-index: -1;
        }

        .container {
            max-width: 500px;
            width: 100%;
            background: var(--card-bg);
            border: 1px solid var(--border);
            border-radius: var(--radius);
            padding: 40px;
            text-align: center;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            animation: slideUp 0.6s cubic-bezier(0.16, 1, 0.3, 1);
        }

        @keyframes slideUp {
            from { transform: translateY(30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .icon-box {
            width: 80px;
            height: 80px;
            background: rgba(248, 113, 113, 0.1);
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            color: var(--primary);
            border: 1px solid rgba(248, 113, 113, 0.2);
        }

        h1 {
            font-size: 24px;
            font-weight: 800;
            margin-bottom: 12px;
            letter-spacing: -0.02em;
        }

        p {
            color: var(--text-dim);
            line-height: 1.6;
            margin-bottom: 32px;
        }

        .info-card {
            background: rgba(255, 255, 255, 0.03);
            border: 1px dashed var(--border);
            border-radius: 12px;
            padding: 16px;
            margin-bottom: 32px;
            text-align: left;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .info-item:last-child {
            margin-bottom: 0;
        }

        .label { color: var(--text-dim); }
        .value { font-weight: 600; color: var(--text-main); }

        .btn {
            display: inline-block;
            width: 100%;
            padding: 16px 24px;
            background: var(--accent);
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.2s ease;
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);
        }

        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(59, 130, 246, 0.4);
            filter: brightness(1.1);
        }

        .footer {
            margin-top: 24px;
            font-size: 12px;
            color: var(--text-dim);
        }

        /* SVG Icon animation */
        .alert-svg {
            animation: pulse 2s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="icon-box">
            <svg class="alert-svg" width="40" height="40" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"></path>
                <line x1="12" y1="9" x2="12" y2="13"></line>
                <line x1="12" y1="17" x2="12.01" y2="17"></line>
            </svg>
        </div>

        <h1>Layanan Terisolasi</h1>
        <p>Maaf, akses internet Anda sementara dinonaktifkan karena terdapat tagihan yang belum terselesaikan. Silakan lakukan pembayaran untuk mengaktifkan kembali layanan secara otomatis.</p>

        <div class="info-card">
            <div class="info-item">
                <span class="label">Status Layanan</span>
                <span class="value" style="color: var(--primary)">Terisolir (Suspend)</span>
            </div>
            <div class="info-item">
                <span class="label">Penyebab</span>
                <span class="value">Tagihan Melewati Jatuh Tempo</span>
            </div>
        </div>

        <a href="/login" class="btn">Cek Tagihan & Bayar Sekarang</a>

        <div class="footer">
            &copy; {{ date('Y') }} PT DJAVVA NETWORK SOLUTION — Reliable Internet Solution
        </div>
    </div>
</body>
</html>
