<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Al-Amena Delivery')</title>
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            background-color: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        .email-container {
            max-width: 600px;
            margin: 20px auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .email-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            padding: 30px 20px;
            text-align: center;
        }
        .email-logo {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        .email-tagline {
            font-size: 14px;
            opacity: 0.9;
        }
        .email-body {
            padding: 30px 20px;
        }
        .email-footer {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            font-size: 12px;
            color: #6c757d;
            border-top: 1px solid #e9ecef;
        }
        h1 {
            color: #333;
            font-size: 24px;
            margin: 0 0 20px 0;
        }
        p {
            margin: 0 0 15px 0;
        }
        .button {
            display: inline-block;
            padding: 12px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff !important;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            margin: 20px 0;
            transition: transform 0.2s;
        }
        .button:hover {
            transform: translateY(-2px);
        }
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #667eea;
            padding: 15px;
            margin: 20px 0;
            border-radius: 4px;
        }
        .info-box strong {
            color: #667eea;
        }
        .social-links {
            margin-top: 15px;
        }
        .social-links a {
            display: inline-block;
            margin: 0 5px;
            color: #6c757d;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <!-- Header -->
        <div class="email-header">
            <div class="email-logo">
                🚚 Al-Amena Delivery
            </div>
            <div class="email-tagline">
                Votre partenaire logistique de confiance
            </div>
        </div>

        <!-- Body -->
        <div class="email-body">
            @yield('content')
        </div>

        <!-- Footer -->
        <div class="email-footer">
            <p><strong>Al-Amena Delivery</strong></p>
            <p>Tunis, Tunisie</p>
            <p>📧 contact@al-amena.tn | 📞 +216 XX XXX XXX</p>
            
            <div class="social-links">
                <a href="#">Facebook</a> •
                <a href="#">Instagram</a> •
                <a href="#">LinkedIn</a>
            </div>

            <p style="margin-top: 15px; font-size: 11px; color: #adb5bd;">
                Vous recevez cet email car vous êtes inscrit sur Al-Amena Delivery.<br>
                Pour modifier vos préférences de notifications, connectez-vous à votre compte.
            </p>
        </div>
    </div>
</body>
</html>
