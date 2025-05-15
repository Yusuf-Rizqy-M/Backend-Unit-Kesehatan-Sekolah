<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            background-color: #eef7f1;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            padding: 20px;
        }
        .email-container {
            max-width: 600px;
            margin: auto;
            padding: 30px;
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 6px 12px rgba(0, 128, 0, 0.15);
            text-align: center;
            border-top: 8px solid #2ecc71;
        }
        h2 {
            color: #2ecc71;
            margin-bottom: 10px;
        }
        p {
            color: #555;
            font-size: 16px;
            margin: 10px 0;
        }
        .otp-code {
            font-size: 32px;
            font-weight: bold;
            color: #27ae60;
            background-color: #eafaf1;
            border: 2px dashed #27ae60;
            padding: 20px 40px;
            display: inline-block;
            border-radius: 10px;
            margin: 25px 0;
            letter-spacing: 4px;
        }
        .footer {
            margin-top: 30px;
            font-size: 12px;
            color: #888;
        }
    </style>
</head>
<body>
    <div class="email-container">
        <h2>One-Time Password (OTP)</h2>
        <p>Gunakan kode di bawah ini untuk memverifikasi login kamu:</p>
        <div class="otp-code">{{ $otp }}</div>
        <p>Kode OTP ini berlaku selama <strong>15 menit</strong>.</p>
        <p class="footer">Jika kamu tidak meminta kode ini, abaikan saja email ini.</p>
    </div>
</body>
</html>
