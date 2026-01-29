<?php
/**
 * MR HASAR DANIŞMANLIK - GİRİŞ SAYFASI
 * MÜKEMMELLİK BİZİ HER ZAMAN AYIRT EDER
 * v5.0 - Final Theme System (Light/Dark)
 */

require_once __DIR__ . '/config.php';
startSecureSession();

// Logout işlemi
if (isset($_GET['logout'])) {
    $_SESSION = [];
    if (ini_get("session.use_cookies")) {
        $params = session_get_cookie_params();
        setcookie(session_name(), '', time() - 42000,
            $params["path"], $params["domain"],
            $params["secure"], $params["httponly"]
        );
    }
    session_destroy();
    header('Location: index.php');
    exit;
}

// Zaten giriş yapmışsa Dashboard'a yönlendir
if (isLoggedIn()) {
    header('Location: modules/dashboard.php');
    exit;
}

// Tema tercihini cookie'den al (varsayılan: dark)
$currentTheme = isset($_COOKIE['mr_theme']) ? $_COOKIE['mr_theme'] : 'dark';
$isLightMode = ($currentTheme === 'light');

$error = '';

// Form gönderildi
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = strtoupper(trim($_POST['username'] ?? ''));
    $password = $_POST['password'] ?? '';

    if (!empty($username) && !empty($password)) {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM users WHERE username = ? AND is_active = 1");
            $stmt->execute([$username]);
            $user = $stmt->fetch();

            // Şifre alanı için uyumluluk (password veya password_hash)
            $passwordField = isset($user['password_hash']) ? $user['password_hash'] : ($user['password'] ?? '');

            if ($user && password_verify($password, $passwordField)) {
                // Giriş başarılı
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'] ?? $user['name'] ?? $user['username'];
                $_SESSION['user_role'] = $user['role'];
                $_SESSION['username'] = $user['username'];

                // Denetim günlüğü
                auditLog('login', $user['id'], 'LOGIN');

                header('Location: modules/dashboard.php');
                exit;
            } else {
                $error = 'KULLANICI ADI VEYA ŞİFRE HATALI';
            }
        } catch (Exception $e) {
            $error = 'SİSTEM HATASI: ' . $e->getMessage();
        }
    } else {
        $error = 'KULLANICI ADI VE ŞİFRE GİRİNİZ';
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - GİRİŞ</title>
    <link rel="icon" type="image/png" href="assets/logos/logo-blue.png">
    <link rel="shortcut icon" type="image/png" href="assets/logos/logo-blue.png">
    <link rel="apple-touch-icon" href="assets/logos/logo-blue.png">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=Manrope:wght@600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script>
        (function() {
            var theme = document.cookie.match(/mr_theme=([^;]+)/);
            theme = theme ? theme[1] : 'dark';
            if (theme === 'light') {
                document.documentElement.classList.add('light-mode');
            }
        })();
    </script>
    <style>
        :root {
            /* Koyu Mod Renkleri (Varsayılan) */
            --bg-body: #0a1628;
            --bg-card: #111d35;
            --bg-input: #0d1a2d;
            --border: #1e3a5f;
            --text: #ffffff;
            --text2: #94a3b8;
            --text3: #64748b;

            /* Marka Renkleri */
            --mr-navy: #1e3a5f;
            --mr-blue: #3b82f6;
            --mr-cyan: #0ea5e9;

            /* Gradient */
            --gradient-btn: linear-gradient(135deg, #6366f1 0%, #3b82f6 50%, #0ea5e9 100%);
            --gradient-btn-hover: linear-gradient(135deg, #4f46e5 0%, #2563eb 50%, #0284c7 100%);

            /* Geçiş */
            --transition: all 0.3s ease;
        }

        /* Açık Mod */
        .light-mode {
            --bg-body: #f1f5f9;
            --bg-card: #ffffff;
            --bg-input: #e2e8f0;
            --border: #cbd5e1;
            --text: #1e293b;
            --text2: #64748b;
            --text3: #94a3b8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-body);
            background-image: url('assets/images/login-bg.jpg');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            padding: 20px;
            position: relative;
        }

        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(10, 22, 40, 0.7);
            z-index: 0;
        }

        .light-mode::before {
            background: rgba(241, 245, 249, 0.85);
        }

        .login-container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 420px;
            display: flex;
            flex-direction: column;
            align-items: center;
        }

        /* Theme Toggle */
        .theme-toggle-login {
            display: flex;
            justify-content: center;
            margin-bottom: 30px;
        }

        .theme-btn-login {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            border: 1px solid var(--border);
            cursor: pointer;
            font-size: 18px;
            transition: var(--transition);
            background: rgba(59, 130, 246, 0.1);
            color: var(--mr-blue);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .theme-btn-login:hover {
            background: rgba(59, 130, 246, 0.2);
            transform: scale(1.05);
        }

        .light-mode .theme-btn-login {
            background: rgba(245, 158, 11, 0.15);
            border-color: rgba(245, 158, 11, 0.3);
            color: #f59e0b;
        }

        .light-mode .theme-btn-login:hover {
            background: rgba(245, 158, 11, 0.25);
        }

        /* Login Box - Glassmorphism */
        .login-box {
            background: rgba(17, 29, 53, 0.85);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 24px;
            padding: 45px 40px;
            transition: var(--transition);
            box-shadow:
                0 25px 60px rgba(0, 0, 0, 0.5),
                0 0 40px rgba(59, 130, 246, 0.15),
                inset 0 1px 1px rgba(255, 255, 255, 0.1);
            max-width: 420px;
            width: 100%;
        }

        .light-mode .login-box {
            background: rgba(255, 255, 255, 0.9);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
            border: 1px solid rgba(59, 130, 246, 0.2);
            box-shadow:
                0 25px 60px rgba(0, 0, 0, 0.15),
                0 0 40px rgba(59, 130, 246, 0.1),
                inset 0 1px 1px rgba(255, 255, 255, 0.8);
        }

        /* Login Icon */
        .login-icon {
            width: 90px;
            height: 90px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            margin: 0 auto 25px;
            background: var(--gradient-btn);
            color: #fff;
            box-shadow: 0 8px 30px rgba(99, 102, 241, 0.4);
            font-family: 'Manrope', sans-serif;
            font-weight: 800;
        }

        /* Login Title */
        .login-title {
            text-align: center;
            font-size: 20px;
            font-weight: 800;
            color: var(--text);
            margin-bottom: 8px;
            letter-spacing: 1px;
            text-transform: uppercase;
        }

        .login-slogan {
            text-align: center;
            font-size: 10px;
            color: #f59e0b;
            margin-bottom: 35px;
            letter-spacing: 3px;
            font-weight: 600;
            text-transform: uppercase;
        }

        /* Alert */
        .alert {
            padding: 16px 18px;
            border-radius: 12px;
            margin-bottom: 25px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #ef4444;
        }

        /* Form Group */
        .frm-grp {
            margin-bottom: 22px;
        }

        .frm-lbl {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 11px;
            color: var(--text2);
            margin-bottom: 10px;
            font-weight: 600;
            letter-spacing: 0.5px;
            text-transform: uppercase;
        }

        .frm-lbl i {
            color: var(--mr-blue);
        }

        /* Form Input - Glassmorphism */
        .frm-in {
            width: 100%;
            padding: 16px 20px;
            background: rgba(13, 26, 45, 0.6);
            border: 1px solid rgba(59, 130, 246, 0.3);
            border-radius: 12px;
            color: var(--text);
            font-size: 14px;
            font-weight: 500;
            transition: var(--transition);
            outline: none;
            backdrop-filter: blur(10px);
        }

        .light-mode .frm-in {
            background: rgba(226, 232, 240, 0.7);
            border-color: rgba(59, 130, 246, 0.2);
        }

        .frm-in:focus {
            border-color: var(--mr-blue);
            box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.2), 0 0 20px rgba(59, 130, 246, 0.15);
        }

        .frm-in::placeholder {
            color: var(--text3);
            font-weight: 400;
        }

        /* Login Button */
        .login-btn {
            width: 100%;
            padding: 18px;
            border: none;
            border-radius: 12px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            background: var(--gradient-btn);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: var(--transition);
            margin-top: 15px;
            letter-spacing: 1px;
            text-transform: uppercase;
            box-shadow: 0 8px 25px rgba(99, 102, 241, 0.4);
        }

        .login-btn:hover {
            transform: translateY(-3px);
            background: var(--gradient-btn-hover);
            box-shadow: 0 12px 35px rgba(99, 102, 241, 0.5);
        }

        /* Login Footer */
        .login-footer {
            text-align: center;
            margin-top: 30px;
            color: var(--text3);
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 1px;
        }

        /* Decorative Elements - subtle glow */
        .login-decor {
            position: fixed;
            border-radius: 50%;
            opacity: 0.15;
            pointer-events: none;
            background: radial-gradient(circle, rgba(59, 130, 246, 0.5) 0%, transparent 70%);
            z-index: 0;
        }

        .light-mode .login-decor {
            opacity: 0.1;
        }

        .login-decor-1 {
            width: 500px;
            height: 500px;
            top: -150px;
            right: -150px;
        }

        .login-decor-2 {
            width: 400px;
            height: 400px;
            bottom: -100px;
            left: -100px;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-box {
                padding: 35px 25px;
            }

            .login-icon {
                width: 70px;
                height: 70px;
                font-size: 28px;
            }

            .login-title {
                font-size: 18px;
            }
        }
    </style>
</head>
<body class="<?= $isLightMode ? 'light-mode' : '' ?>">
    <!-- Decorative Elements -->
    <div class="login-decor login-decor-1"></div>
    <div class="login-decor login-decor-2"></div>

    <div class="login-container">
        <!-- Theme Toggle -->
        <div class="theme-toggle-login">
            <button type="button" class="theme-btn-login" id="themeToggleBtn" onclick="toggleTheme()" title="Tema Değiştir">
                <i class="fas fa-moon" id="themeIcon"></i>
            </button>
        </div>

        <div class="login-box">
            <div class="login-icon">MR</div>
            <div class="login-title"><?= APP_NAME ?></div>
            <div class="login-slogan"><?= APP_SLOGAN ?></div>

            <?php if ($error): ?>
            <div class="alert">
                <i class="fas fa-exclamation-circle"></i>
                <?= e($error) ?>
            </div>
            <?php endif; ?>

            <form method="POST" autocomplete="off">
                <div class="frm-grp">
                    <label class="frm-lbl">
                        <i class="fas fa-user"></i> KULLANICI ADI
                    </label>
                    <input type="text" name="username" class="frm-in" required autofocus placeholder="Kullanıcı adınızı girin">
                </div>
                <div class="frm-grp">
                    <label class="frm-lbl">
                        <i class="fas fa-lock"></i> ŞİFRE
                    </label>
                    <input type="password" name="password" class="frm-in" required placeholder="Şifrenizi girin">
                </div>
                <button type="submit" class="login-btn">
                    <i class="fas fa-sign-in-alt"></i> GİRİŞ YAP
                </button>
            </form>

            <div class="login-footer">
                V<?= APP_VERSION ?> &copy; <?= date('Y') ?> - TÜM HAKLARI SAKLIDIR
            </div>
        </div>
    </div>

    <script>
        function toggleTheme() {
            var body = document.body;
            var html = document.documentElement;
            var icon = document.getElementById('themeIcon');

            if (body.classList.contains('light-mode')) {
                body.classList.remove('light-mode');
                html.classList.remove('light-mode');
                icon.classList.remove('fa-sun');
                icon.classList.add('fa-moon');
                document.cookie = 'mr_theme=dark;path=/;max-age=31536000;SameSite=Lax';
            } else {
                body.classList.add('light-mode');
                html.classList.add('light-mode');
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
                document.cookie = 'mr_theme=light;path=/;max-age=31536000;SameSite=Lax';
            }
        }

        // Sayfa yüklendiğinde tema ikonunu güncelle
        document.addEventListener('DOMContentLoaded', function() {
            var icon = document.getElementById('themeIcon');
            if (document.body.classList.contains('light-mode')) {
                icon.classList.remove('fa-moon');
                icon.classList.add('fa-sun');
            }
        });
    </script>
</body>
</html>
