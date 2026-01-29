<?php
/**
 * MR HASAR DANIŞMANLIK - Giriş Sayfası
 */

require_once 'config.php';

startSecureSession();

// Zaten giriş yapmışsa yönlendir
if (isLoggedIn()) {
    header('Location: modules/dashboard.php');
    exit;
}

$error = '';
$username = '';

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Kullanıcı adı ve şifre gereklidir.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user && (password_verify($password, $user['password']) || $password === $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            $stmt = $db->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);

            auditLog('users', $user['id'], 'LOGIN');

            header('Location: modules/dashboard.php');
            exit;
        } else {
            $error = 'Geçersiz kullanıcı adı veya şifre.';
        }
    }
}
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
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            min-height: 100vh;
            width: 100vw;
            background: linear-gradient(135deg, #0a0a1a 0%, #0d1b2a 25%, #1b263b 50%, #0d1b2a 75%, #0a0a1a 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        /* Animasyonlu Parçacıklar */
        .particles {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 0;
            pointer-events: none;
        }

        .particle {
            position: absolute;
            width: 3px;
            height: 3px;
            background: rgba(0, 212, 255, 0.8);
            border-radius: 50%;
            box-shadow: 0 0 10px rgba(0, 212, 255, 0.5), 0 0 20px rgba(0, 212, 255, 0.3);
            animation: float 15s infinite ease-in-out;
        }

        .particle:nth-child(1) { left: 5%; top: 10%; animation-delay: 0s; animation-duration: 12s; }
        .particle:nth-child(2) { left: 15%; top: 30%; animation-delay: 1s; animation-duration: 18s; width: 2px; height: 2px; }
        .particle:nth-child(3) { left: 25%; top: 60%; animation-delay: 2s; animation-duration: 14s; }
        .particle:nth-child(4) { left: 35%; top: 20%; animation-delay: 3s; animation-duration: 16s; width: 4px; height: 4px; }
        .particle:nth-child(5) { left: 45%; top: 80%; animation-delay: 4s; animation-duration: 13s; width: 2px; height: 2px; }
        .particle:nth-child(6) { left: 55%; top: 40%; animation-delay: 5s; animation-duration: 17s; }
        .particle:nth-child(7) { left: 65%; top: 70%; animation-delay: 6s; animation-duration: 15s; width: 4px; height: 4px; }
        .particle:nth-child(8) { left: 75%; top: 15%; animation-delay: 7s; animation-duration: 19s; }
        .particle:nth-child(9) { left: 85%; top: 50%; animation-delay: 8s; animation-duration: 11s; width: 2px; height: 2px; }
        .particle:nth-child(10) { left: 95%; top: 85%; animation-delay: 9s; animation-duration: 14s; }
        .particle:nth-child(11) { left: 10%; top: 45%; animation-delay: 0.5s; animation-duration: 16s; background: rgba(0, 255, 204, 0.6); }
        .particle:nth-child(12) { left: 20%; top: 75%; animation-delay: 1.5s; animation-duration: 13s; background: rgba(0, 255, 204, 0.6); width: 3px; height: 3px; }
        .particle:nth-child(13) { left: 30%; top: 5%; animation-delay: 2.5s; animation-duration: 17s; background: rgba(100, 200, 255, 0.7); }
        .particle:nth-child(14) { left: 40%; top: 55%; animation-delay: 3.5s; animation-duration: 12s; background: rgba(100, 200, 255, 0.7); width: 2px; height: 2px; }
        .particle:nth-child(15) { left: 50%; top: 25%; animation-delay: 4.5s; animation-duration: 18s; }
        .particle:nth-child(16) { left: 60%; top: 90%; animation-delay: 5.5s; animation-duration: 14s; background: rgba(0, 255, 204, 0.6); width: 4px; height: 4px; }
        .particle:nth-child(17) { left: 70%; top: 35%; animation-delay: 6.5s; animation-duration: 15s; }
        .particle:nth-child(18) { left: 80%; top: 65%; animation-delay: 7.5s; animation-duration: 16s; background: rgba(100, 200, 255, 0.7); }
        .particle:nth-child(19) { left: 90%; top: 8%; animation-delay: 8.5s; animation-duration: 13s; width: 3px; height: 3px; }
        .particle:nth-child(20) { left: 3%; top: 92%; animation-delay: 9.5s; animation-duration: 17s; background: rgba(0, 255, 204, 0.6); }

        /* Yıldızlar */
        .stars {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
            pointer-events: none;
        }

        .star {
            position: absolute;
            width: 2px;
            height: 2px;
            background: #fff;
            border-radius: 50%;
            animation: twinkle 3s infinite ease-in-out;
        }

        .star:nth-child(1) { left: 2%; top: 5%; animation-delay: 0s; }
        .star:nth-child(2) { left: 8%; top: 15%; animation-delay: 0.3s; width: 1px; height: 1px; }
        .star:nth-child(3) { left: 12%; top: 25%; animation-delay: 0.6s; }
        .star:nth-child(4) { left: 18%; top: 8%; animation-delay: 0.9s; width: 3px; height: 3px; }
        .star:nth-child(5) { left: 22%; top: 45%; animation-delay: 1.2s; width: 1px; height: 1px; }
        .star:nth-child(6) { left: 28%; top: 70%; animation-delay: 1.5s; }
        .star:nth-child(7) { left: 32%; top: 12%; animation-delay: 1.8s; }
        .star:nth-child(8) { left: 38%; top: 88%; animation-delay: 2.1s; width: 1px; height: 1px; }
        .star:nth-child(9) { left: 42%; top: 35%; animation-delay: 2.4s; }
        .star:nth-child(10) { left: 48%; top: 62%; animation-delay: 2.7s; width: 3px; height: 3px; }
        .star:nth-child(11) { left: 52%; top: 18%; animation-delay: 0.15s; }
        .star:nth-child(12) { left: 58%; top: 78%; animation-delay: 0.45s; width: 1px; height: 1px; }
        .star:nth-child(13) { left: 62%; top: 42%; animation-delay: 0.75s; }
        .star:nth-child(14) { left: 68%; top: 6%; animation-delay: 1.05s; }
        .star:nth-child(15) { left: 72%; top: 55%; animation-delay: 1.35s; width: 1px; height: 1px; }
        .star:nth-child(16) { left: 78%; top: 82%; animation-delay: 1.65s; width: 3px; height: 3px; }
        .star:nth-child(17) { left: 82%; top: 28%; animation-delay: 1.95s; }
        .star:nth-child(18) { left: 88%; top: 48%; animation-delay: 2.25s; width: 1px; height: 1px; }
        .star:nth-child(19) { left: 92%; top: 72%; animation-delay: 2.55s; }
        .star:nth-child(20) { left: 96%; top: 15%; animation-delay: 2.85s; }
        .star:nth-child(21) { left: 5%; top: 58%; animation-delay: 0.2s; }
        .star:nth-child(22) { left: 15%; top: 92%; animation-delay: 0.5s; width: 1px; height: 1px; }
        .star:nth-child(23) { left: 25%; top: 32%; animation-delay: 0.8s; }
        .star:nth-child(24) { left: 35%; top: 3%; animation-delay: 1.1s; width: 3px; height: 3px; }
        .star:nth-child(25) { left: 45%; top: 95%; animation-delay: 1.4s; }
        .star:nth-child(26) { left: 55%; top: 52%; animation-delay: 1.7s; width: 1px; height: 1px; }
        .star:nth-child(27) { left: 65%; top: 22%; animation-delay: 2s; }
        .star:nth-child(28) { left: 75%; top: 68%; animation-delay: 2.3s; }
        .star:nth-child(29) { left: 85%; top: 38%; animation-delay: 2.6s; width: 1px; height: 1px; }
        .star:nth-child(30) { left: 95%; top: 58%; animation-delay: 2.9s; width: 3px; height: 3px; }

        @keyframes float {
            0%, 100% {
                transform: translateY(0) translateX(0) scale(1);
                opacity: 0.8;
            }
            25% {
                transform: translateY(-30px) translateX(15px) scale(1.1);
                opacity: 1;
            }
            50% {
                transform: translateY(-15px) translateX(-10px) scale(0.9);
                opacity: 0.6;
            }
            75% {
                transform: translateY(-40px) translateX(20px) scale(1.05);
                opacity: 0.9;
            }
        }

        @keyframes twinkle {
            0%, 100% { opacity: 0.3; transform: scale(1); }
            50% { opacity: 1; transform: scale(1.2); }
        }

        /* Parlayan çizgiler (shooting stars) */
        .shooting-star {
            position: fixed;
            width: 100px;
            height: 2px;
            background: linear-gradient(90deg, rgba(0, 212, 255, 0), rgba(0, 212, 255, 1), rgba(255, 255, 255, 1));
            border-radius: 50%;
            animation: shoot 8s infinite linear;
            opacity: 0;
            z-index: 0;
            pointer-events: none;
        }

        .shooting-star:nth-child(1) { top: 10%; left: 100%; animation-delay: 0s; }
        .shooting-star:nth-child(2) { top: 30%; left: 100%; animation-delay: 3s; }
        .shooting-star:nth-child(3) { top: 60%; left: 100%; animation-delay: 6s; }

        @keyframes shoot {
            0% { transform: translateX(0) translateY(0) rotate(-45deg); opacity: 0; }
            5% { opacity: 1; }
            15% { transform: translateX(-800px) translateY(200px) rotate(-45deg); opacity: 0; }
            100% { transform: translateX(-800px) translateY(200px) rotate(-45deg); opacity: 0; }
        }
        .login-wrapper { width: 100%; max-width: 350px; padding: 20px; background: transparent; position: relative; z-index: 10; }
        .login-form { background: transparent; padding: 40px 30px; border: none; box-shadow: none; }
        .form-group { margin-bottom: 25px; position: relative; }
        .form-group label { display: block; color: rgba(255, 255, 255, 0.9); font-size: 0.9rem; margin-bottom: 8px; font-weight: 500; }
        .form-group .input-wrapper { position: relative; }
        .form-group .input-wrapper i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: rgba(255, 255, 255, 0.5); font-size: 1.1rem; z-index: 2; transition: color 0.3s ease; }
        .form-group input { width: 100%; padding: 15px 15px 15px 45px; background: transparent; border: 1px solid rgba(255, 255, 255, 0.3); border-radius: 10px; color: #fff; font-size: 1rem; transition: all 0.3s ease; }
        .form-group input::placeholder { color: rgba(255, 255, 255, 0.5); }
        .form-group input:not(:placeholder-shown) { background: rgba(255, 255, 255, 1); color: #1a1a2e; border-color: rgba(255, 255, 255, 0.9); }
        .form-group input:focus { background: rgba(255, 255, 255, 1); color: #1a1a2e; border-color: rgba(255, 255, 255, 0.9); outline: none; }
        .form-group input:focus::placeholder { color: rgba(0, 0, 0, 0.3); }
        .form-group .input-wrapper:has(input:focus) i, .form-group .input-wrapper:has(input:not(:placeholder-shown)) i { color: #1a1a2e; }
        .form-check { margin-bottom: 25px; }
        .form-check-label { color: rgba(255, 255, 255, 0.8); font-size: 0.9rem; }
        .form-check-input { background: transparent; border: 1px solid rgba(255, 255, 255, 0.4); }
        .form-check-input:checked { background: linear-gradient(135deg, #00d4ff, #0099cc); border-color: #00d4ff; }
        .btn-login { width: 100%; padding: 16px; border: none; border-radius: 10px; font-size: 1.1rem; font-weight: 600; cursor: pointer; position: relative; overflow: hidden; background: linear-gradient(135deg, #0a2540 0%, #0d3a5c 25%, #1a5a7a 50%, #0d3a5c 75%, #0a2540 100%); background-size: 200% 200%; color: #fff; text-transform: uppercase; letter-spacing: 2px; box-shadow: 0 0 20px rgba(0, 212, 255, 0.3), 0 0 40px rgba(0, 212, 255, 0.2), inset 0 0 20px rgba(0, 212, 255, 0.1); animation: holographic 3s ease-in-out infinite; transition: all 0.3s ease; }
        .btn-login::before { content: ''; position: absolute; top: 0; left: -100%; width: 100%; height: 100%; background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent); animation: shine 2s infinite; }
        .btn-login::after { content: ''; position: absolute; top: -2px; left: -2px; right: -2px; bottom: -2px; background: linear-gradient(45deg, #00d4ff, #0099cc, #00ffcc, #00d4ff); background-size: 400% 400%; border-radius: 12px; z-index: -1; animation: borderGlow 3s ease-in-out infinite; opacity: 0.7; }
        .btn-login:hover { transform: translateY(-3px) scale(1.02); box-shadow: 0 0 30px rgba(0, 212, 255, 0.5), 0 0 60px rgba(0, 212, 255, 0.3), 0 10px 40px rgba(0, 0, 0, 0.3), inset 0 0 30px rgba(0, 212, 255, 0.2); }
        .btn-login:active { transform: translateY(-1px) scale(0.98); }
        .btn-login i { margin-right: 10px; font-size: 1.2rem; }
        @keyframes holographic { 0%, 100% { background-position: 0% 50%; } 50% { background-position: 100% 50%; } }
        @keyframes shine { 0% { left: -100%; } 50%, 100% { left: 100%; } }
        @keyframes borderGlow { 0%, 100% { background-position: 0% 50%; opacity: 0.5; } 50% { background-position: 100% 50%; opacity: 0.8; } }
        .alert { background: rgba(220, 53, 69, 0.2); border: 1px solid rgba(220, 53, 69, 0.5); color: #ff6b6b; border-radius: 10px; padding: 12px 15px; margin-bottom: 20px; font-size: 0.9rem; }
        .alert i { margin-right: 8px; }
        .login-icon { text-align: center; margin-bottom: 30px; }
        .login-icon .icon-3d { width: 80px; height: 80px; margin: 0 auto; background: linear-gradient(135deg, rgba(0, 212, 255, 0.2), rgba(0, 153, 204, 0.1)); border-radius: 50%; display: flex; align-items: center; justify-content: center; position: relative; box-shadow: 0 0 30px rgba(0, 212, 255, 0.3), inset 0 0 20px rgba(0, 212, 255, 0.1); animation: pulse3d 2s ease-in-out infinite; }
        .login-icon .icon-3d i { font-size: 2.5rem; background: linear-gradient(135deg, #00d4ff, #00ffcc); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .login-icon .icon-3d::before { content: ''; position: absolute; width: 100%; height: 100%; border-radius: 50%; border: 2px solid rgba(0, 212, 255, 0.3); animation: rotate3d 4s linear infinite; }
        @keyframes pulse3d { 0%, 100% { transform: scale(1); box-shadow: 0 0 30px rgba(0, 212, 255, 0.3), inset 0 0 20px rgba(0, 212, 255, 0.1); } 50% { transform: scale(1.05); box-shadow: 0 0 50px rgba(0, 212, 255, 0.5), inset 0 0 30px rgba(0, 212, 255, 0.2); } }
        @keyframes rotate3d { 0% { transform: rotateZ(0deg); } 100% { transform: rotateZ(360deg); } }
        @media (max-width: 768px) {
            body { justify-content: center; padding: 20px; }
            .login-wrapper { max-width: 100%; background: transparent; }
            .login-form { padding: 30px 20px; background: transparent; }
            .particle { width: 2px !important; height: 2px !important; }
        }
    </style>
</head>
<body>
    <!-- Animasyonlu Parçacıklar -->
    <div class="particles">
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
        <div class="particle"></div>
    </div>

    <!-- Yıldızlar -->
    <div class="stars">
        <div class="star"></div>
        <div class="star"></div>
        <div class="star"></div>
        <div class="star"></div>
        <div class="star"></div>
        <div class="star"></div>
        <div class="star"></div>
        <div class="star"></div>
        <div class="star"></div>
        <div class="star"></div>
        <div class="star"></div>
        <div class="star"></div>
        <div class="star"></div>
        <div class="star"></div>
        <div class="star"></div>
        <div class="star"></div>
        <div class="star"></div>
        <div class="star"></div>
        <div class="star"></div>
        <div class="star"></div>
        <div class="star"></div>
        <div class="star"></div>
        <div class="star"></div>
        <div class="star"></div>
        <div class="star"></div>
        <div class="star"></div>
        <div class="star"></div>
        <div class="star"></div>
        <div class="star"></div>
        <div class="star"></div>
    </div>

    <!-- Kayan Yıldızlar -->
    <div class="shooting-star"></div>
    <div class="shooting-star"></div>
    <div class="shooting-star"></div>

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
