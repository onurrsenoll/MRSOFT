<?php
/**
 * MR HASAR DANIŞMANLIK - Giriş Sayfası
 * Admin login sayfasına yönlendir
 */

header('Location: admin/login.php');
exit;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giriş - <?= APP_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            min-height: 100vh;
            width: 100vw;
            background: #020c1b url('admin/img/login-bg.png') no-repeat center center;
            background-size: 100% 100%;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 3%;
            overflow: hidden;
        }

        .login-wrapper {
            width: 100%;
            max-width: 350px;
            padding: 20px;
            background: transparent;
        }

        .login-form {
            background: transparent;
            padding: 40px 30px;
            border: none;
            box-shadow: none;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            color: rgba(255, 255, 255, 0.9);
            font-size: 0.9rem;
            margin-bottom: 8px;
            font-weight: 500;
        }

        .form-group .input-wrapper {
            position: relative;
        }

        .form-group .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.5);
            font-size: 1.1rem;
            z-index: 2;
            transition: color 0.3s ease;
        }

        .form-group input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 10px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .form-group input:not(:placeholder-shown) {
            background: rgba(255, 255, 255, 1);
            color: #1a1a2e;
            border-color: rgba(255, 255, 255, 0.9);
        }

        .form-group input:focus {
            background: rgba(255, 255, 255, 1);
            color: #1a1a2e;
            border-color: rgba(255, 255, 255, 0.9);
            outline: none;
        }

        .form-group input:focus::placeholder {
            color: rgba(0, 0, 0, 0.3);
        }

        .form-group .input-wrapper:has(input:focus) i,
        .form-group .input-wrapper:has(input:not(:placeholder-shown)) i {
            color: #1a1a2e;
        }

        .form-check {
            margin-bottom: 25px;
        }

        .form-check-label {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
        }

        .form-check-input {
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.4);
        }

        .form-check-input:checked {
            background: linear-gradient(135deg, #00d4ff, #0099cc);
            border-color: #00d4ff;
        }

        .btn-login {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 10px;
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            background: linear-gradient(135deg, #0a2540 0%, #0d3a5c 25%, #1a5a7a 50%, #0d3a5c 75%, #0a2540 100%);
            background-size: 200% 200%;
            color: #fff;
            text-transform: uppercase;
            letter-spacing: 2px;
            box-shadow: 0 0 20px rgba(0, 212, 255, 0.3), 0 0 40px rgba(0, 212, 255, 0.2), inset 0 0 20px rgba(0, 212, 255, 0.1);
            animation: holographic 3s ease-in-out infinite;
            transition: all 0.3s ease;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            animation: shine 2s infinite;
        }

        .btn-login::after {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #00d4ff, #0099cc, #00ffcc, #00d4ff);
            background-size: 400% 400%;
            border-radius: 12px;
            z-index: -1;
            animation: borderGlow 3s ease-in-out infinite;
            opacity: 0.7;
        }

        .btn-login:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 0 30px rgba(0, 212, 255, 0.5), 0 0 60px rgba(0, 212, 255, 0.3), 0 10px 40px rgba(0, 0, 0, 0.3), inset 0 0 30px rgba(0, 212, 255, 0.2);
        }

        .btn-login:active {
            transform: translateY(-1px) scale(0.98);
        }

        .btn-login i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        @keyframes holographic {
            0%, 100% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
        }

        @keyframes shine {
            0% { left: -100%; }
            50%, 100% { left: 100%; }
        }

        @keyframes borderGlow {
            0%, 100% { background-position: 0% 50%; opacity: 0.5; }
            50% { background-position: 100% 50%; opacity: 0.8; }
        }

        .alert {
            background: rgba(220, 53, 69, 0.2);
            border: 1px solid rgba(220, 53, 69, 0.5);
            color: #ff6b6b;
            border-radius: 10px;
            padding: 12px 15px;
            margin-bottom: 20px;
            font-size: 0.9rem;
        }

        .alert i {
            margin-right: 8px;
        }

        .login-icon {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-icon .icon-3d {
            width: 80px;
            height: 80px;
            margin: 0 auto;
            background: linear-gradient(135deg, rgba(0, 212, 255, 0.2), rgba(0, 153, 204, 0.1));
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            box-shadow: 0 0 30px rgba(0, 212, 255, 0.3), inset 0 0 20px rgba(0, 212, 255, 0.1);
            animation: pulse3d 2s ease-in-out infinite;
        }

        .login-icon .icon-3d i {
            font-size: 2.5rem;
            background: linear-gradient(135deg, #00d4ff, #00ffcc);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .login-icon .icon-3d::before {
            content: '';
            position: absolute;
            width: 100%;
            height: 100%;
            border-radius: 50%;
            border: 2px solid rgba(0, 212, 255, 0.3);
            animation: rotate3d 4s linear infinite;
        }

        @keyframes pulse3d {
            0%, 100% { transform: scale(1); box-shadow: 0 0 30px rgba(0, 212, 255, 0.3), inset 0 0 20px rgba(0, 212, 255, 0.1); }
            50% { transform: scale(1.05); box-shadow: 0 0 50px rgba(0, 212, 255, 0.5), inset 0 0 30px rgba(0, 212, 255, 0.2); }
        }

        @keyframes rotate3d {
            0% { transform: rotateZ(0deg); }
            100% { transform: rotateZ(360deg); }
        }

        @media (max-width: 768px) {
            body {
                justify-content: center;
                padding: 20px;
                background-size: cover;
                background-position: center;
            }
            .login-wrapper { max-width: 100%; background: transparent; }
            .login-form { padding: 30px 20px; background: transparent; }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-form">
            <div class="login-icon">
                <div class="icon-3d">
                    <i class="bi bi-person-circle"></i>
                </div>
            </div>

            <?php if ($error): ?>
                <div class="alert">
                    <i class="bi bi-exclamation-triangle-fill"></i>
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label>Kullanıcı Adı</label>
                    <div class="input-wrapper">
                        <i class="bi bi-person"></i>
                        <input type="text" name="username" placeholder="Kullanıcı adınızı girin" value="<?= e($username) ?>" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label>Şifre</label>
                    <div class="input-wrapper">
                        <i class="bi bi-lock"></i>
                        <input type="password" name="password" placeholder="Şifrenizi girin" required>
                    </div>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Beni hatırla</label>
                </div>

                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Giriş Yap
                </button>
            </form>
        </div>
    </div>
</body>
</html>
