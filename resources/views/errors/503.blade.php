<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Maintenance Mode - PT Djavva Network Solution</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;700&display=swap" rel="stylesheet">
    <style>
        body {
            margin: 0;
            padding: 0;
            background: #000;
            font-family: 'Poppins', sans-serif;
        }
        * {
            box-sizing: border-box;
        }
        .maintenance {
            background-image: url(https://demo.wpbeaveraddons.com/wp-content/uploads/2018/02/main-1.jpg);
            background-repeat: no-repeat;
            background-position: center center;
            background-attachment: fixed;
            background-size: cover;
            width: 100%;
            height: 100%;
            min-height: 100vh;
            display: flex;
            flex-flow: column nowrap;
            justify-content: center;
            align-items: center;
            position: relative;
        }
        /* Overlay to ensure text readability */
        .maintenance::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(255, 255, 255, 0.4);
            z-index: 1;
        }

        .maintenance_contain {
            position: relative;
            z-index: 2;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            width: 100%;  
            padding: 30px;
            max-width: 800px;
            background: #ffffff;
            border-radius: 20px;
        }
        .maintenance_contain img {
            width: auto;
            max-width: 250px;
            margin-bottom: 20px;
        }
        .pp-infobox-title-prefix {
            font-weight: 500;
            font-size: 16px;
            color: #002e5d; /* Brand Color */
            letter-spacing: 3px;
            margin-top: 10px;
            text-align: center;
            text-transform: uppercase;
        }
        .pp-infobox-title {
            color: #0f172a;
            font-weight: 700;
            font-size: 36px;
            margin: 15px 0;
            text-align: center;
            line-height: 1.2;
        }
        .pp-infobox-description {
            color: #475569;
            font-weight: 400;
            font-size: 16px;
            margin-bottom: 30px;
            text-align: center;
            line-height: 1.6;
            max-width: 500px;
        }
        .title-text.pp-primary-title {
            color: #002e5d;
            font-weight: 600;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 20px;
        }
        .pp-social-icons {
            display: flex;
            flex-flow: row wrap;
            align-items: center;
            justify-content: center;
            margin-top: 15px;
        }
        .pp-social-icon {
            margin: 0 15px;
            text-align: center;
        }
        .pp-social-icon a {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-decoration: none;
            transition: transform 0.3s ease;
        }
        .pp-social-icon a:hover {
            transform: translateY(-5px);
        }
        .pp-social-icon i {
            border-radius: 50%;
            font-size: 24px;
            height: 50px;
            width: 50px;
            line-height: 50px;
            text-align: center;
            margin-bottom: 8px;
        }
        /* WhatsApp Color */
        .pp-social-icon.wa i {
            color: #25d366;
            border: 2px solid #25d366;
        }
        /* Email Color */
        .pp-social-icon.email i {
            color: #ea4335;
            border: 2px solid #ea4335;
        }
        .pp-social-icon span {
            font-size: 12px;
            font-weight: 600;
            color: #1e293b;
        }

        @media (max-width: 600px) {
            .pp-infobox-title { font-size: 28px; }
            .pp-infobox-description { font-size: 14px; }
            .maintenance_contain { margin: 20px; }
        }
    </style>
</head>
<body>
    <div class="maintenance">
        <div class="maintenance_contain">
            <img src="https://demo.wpbeaveraddons.com/wp-content/uploads/2018/02/main-vector.png" alt="maintenance">
            <span class="pp-infobox-title-prefix">MOHON MENUNGGU</span>
            <div class="pp-infobox-title-wrapper">
                <h3 class="pp-infobox-title">Sistem Sedang Maintenance</h3>
            </div> 
            <div class="pp-infobox-description">
                <p>Kami sedang melakukan pemeliharaan rutin untuk meningkatkan kualitas layanan internet Anda. Mohon maaf atas ketidaknyamanannya.</p>
            </div>    
            <span class="title-text pp-primary-title">Hubungi Kami</span>
            <div class="pp-social-icons">
                <div class="pp-social-icon wa">
                    <a href="https://wa.me/6282246468335" target="_blank" aria-label="WhatsApp">
                        <i class="fa fa-whatsapp"></i>
                        <span>WhatsApp</span>
                    </a>
                </div>
                <div class="pp-social-icon email">
                    <a href="mailto:djavvanetwork@gmail.com" target="_blank" aria-label="Email">
                        <i class="fa fa-envelope"></i>
                        <span>Email Support</span>
                    </a>
                </div>
            </div>
            
            <div style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #f1f5f9; width: 100%; text-align: center;">
                <p style="font-size: 11px; font-weight: 700; color: #64748b; letter-spacing: 2px; margin: 0; text-transform: uppercase;">
                    PT DJAVVA NETWORK SOLUTION
                </p>
                <p style="font-size: 9px; color: #94a3b8; margin-top: 5px;">
                    &copy; {{ date('Y') }} All Rights Reserved.
                </p>
            </div>
        </div>
    </div>
</body>
</html>
