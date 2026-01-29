<?php
/**
 * MR HASAR DANIŞMANLIK - GİRİŞ SAYFASI
 * MÜKEMMELLİK BİZİ HER ZAMAN AYIRT EDER
 * v4.0 - Neumorphism Theme Support
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

// Tema tercihini cookie'den al
$currentTheme = isset($_COOKIE['mr_theme']) ? $_COOKIE['mr_theme'] : 'light';

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
<html lang="tr" data-theme="<?= htmlspecialchars($currentTheme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - GİRİŞ</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <script>
        (function() {
            var theme = document.cookie.match(/mr_theme=([^;]+)/);
            theme = theme ? theme[1] : 'light';
            document.documentElement.setAttribute('data-theme', theme);
        })();
    </script>
    <style>
        :root {
            --mr-navy: #1e3a5f;
            --mr-blue: #2563eb;
            --mr-cyan: #0ea5e9;
            --mr-gradient: linear-gradient(135deg, #1e3a5f 0%, #2563eb 50%, #0ea5e9 100%);
            --mr-gradient-btn: linear-gradient(135deg, #2563eb 0%, #0ea5e9 100%);
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Dark Theme */
        [data-theme="dark"] {
            --bg-primary: #0f172a;
            --bg-card: #1e293b;
            --bg-input: #0f172a;
            --text: #f1f5f9;
            --text-secondary: #94a3b8;
            --text-muted: #64748b;
            --border: rgba(59, 130, 246, 0.2);
            --neu-light: rgba(255, 255, 255, 0.03);
            --neu-dark: rgba(0, 0, 0, 0.5);
            --glow: rgba(59, 130, 246, 0.3);
        }

        /* Light Theme (Neumorphism) */
        [data-theme="light"] {
            --bg-primary: #e4ebf5;
            --bg-card: #e4ebf5;
            --bg-input: #dce4ed;
            --text: #1e3a5f;
            --text-secondary: #475569;
            --text-muted: #64748b;
            --border: transparent;
            --neu-light: #ffffff;
            --neu-dark: rgba(163, 177, 198, 0.6);
            --glow: rgba(37, 99, 235, 0.2);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            text-transform: uppercase;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-primary);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: var(--transition);
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
        }

        /* Theme Toggle */
        .theme-toggle-login {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 30px;
            padding: 6px;
            border-radius: 30px;
            width: fit-content;
            margin-left: auto;
            margin-right: auto;
        }

        [data-theme="dark"] .theme-toggle-login {
            background: var(--bg-card);
            box-shadow:
                inset 3px 3px 6px rgba(0, 0, 0, 0.4),
                inset -2px -2px 5px rgba(255, 255, 255, 0.02);
        }

        [data-theme="light"] .theme-toggle-login {
            background: #dce4ed;
            box-shadow:
                inset 4px 4px 8px rgba(163, 177, 198, 0.5),
                inset -4px -4px 8px rgba(255, 255, 255, 0.9);
        }

        .theme-btn-login {
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: none;
            cursor: pointer;
            font-size: 16px;
            transition: var(--transition);
            background: transparent;
            color: var(--text-muted);
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .theme-btn-login:hover {
            color: var(--text);
        }

        .theme-btn-login.active {
            background: var(--mr-gradient-btn);
            color: #fff;
        }

        [data-theme="dark"] .theme-btn-login.active {
            box-shadow:
                3px 3px 6px rgba(0, 0, 0, 0.3),
                0 0 20px var(--glow);
        }

        [data-theme="light"] .theme-btn-login.active {
            box-shadow:
                4px 4px 8px rgba(163, 177, 198, 0.5),
                -4px -4px 8px rgba(255, 255, 255, 0.8),
                0 0 20px var(--glow);
        }

        /* Login Box */
        .login-box {
            background: var(--bg-card);
            border-radius: 24px;
            padding: 45px 40px;
            transition: var(--transition);
        }

        [data-theme="dark"] .login-box {
            border: 1px solid var(--border);
            box-shadow:
                12px 12px 30px rgba(0, 0, 0, 0.5),
                -6px -6px 20px rgba(255, 255, 255, 0.02);
        }

        [data-theme="light"] .login-box {
            box-shadow:
                12px 12px 30px rgba(163, 177, 198, 0.5),
                -12px -12px 30px rgba(255, 255, 255, 0.9);
        }

        /* Login Icon */
        .login-icon {
            width: 90px;
            height: 90px;
            border-radius: 24px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin: 0 auto 25px;
            background: var(--mr-gradient-btn);
            color: #fff;
            transition: var(--transition);
        }

        [data-theme="dark"] .login-icon {
            box-shadow:
                6px 6px 15px rgba(0, 0, 0, 0.4),
                -3px -3px 10px rgba(255, 255, 255, 0.02),
                0 0 30px var(--glow);
        }

        [data-theme="light"] .login-icon {
            box-shadow:
                8px 8px 20px rgba(163, 177, 198, 0.5),
                -8px -8px 20px rgba(255, 255, 255, 0.8),
                0 0 30px var(--glow);
        }

        /* Login Title */
        .login-title {
            text-align: center;
            font-size: 22px;
            font-weight: 800;
            background: var(--mr-gradient);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 8px;
            letter-spacing: 1px;
        }

        .login-slogan {
            text-align: center;
            font-size: 10px;
            color: #f59e0b;
            margin-bottom: 35px;
            letter-spacing: 3px;
            font-weight: 600;
        }

        /* Alert */
        .alert {
            padding: 16px 18px;
            border-radius: 14px;
            margin-bottom: 25px;
            font-size: 12px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }

        [data-theme="dark"] .alert {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #f87171;
            box-shadow:
                4px 4px 10px rgba(0, 0, 0, 0.3),
                -2px -2px 6px rgba(255, 255, 255, 0.02);
        }

        [data-theme="light"] .alert {
            background: rgba(239, 68, 68, 0.08);
            color: #dc2626;
            box-shadow:
                4px 4px 10px rgba(163, 177, 198, 0.4),
                -4px -4px 10px rgba(255, 255, 255, 0.8);
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
            color: var(--text-secondary);
            margin-bottom: 10px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .frm-lbl i {
            color: var(--mr-blue);
        }

        /* Form Input */
        .frm-in {
            width: 100%;
            padding: 16px 20px;
            background: var(--bg-input);
            border: 1px solid var(--border);
            border-radius: 14px;
            color: var(--text);
            font-size: 14px;
            font-weight: 500;
            transition: var(--transition);
            outline: none;
        }

        [data-theme="dark"] .frm-in {
            box-shadow:
                inset 4px 4px 8px rgba(0, 0, 0, 0.3),
                inset -2px -2px 6px rgba(255, 255, 255, 0.02);
        }

        [data-theme="light"] .frm-in {
            box-shadow:
                inset 5px 5px 10px rgba(163, 177, 198, 0.5),
                inset -5px -5px 10px rgba(255, 255, 255, 0.9);
        }

        .frm-in:focus {
            border-color: var(--mr-blue);
        }

        [data-theme="dark"] .frm-in:focus {
            box-shadow:
                inset 4px 4px 8px rgba(0, 0, 0, 0.3),
                inset -2px -2px 6px rgba(255, 255, 255, 0.02),
                0 0 0 3px rgba(37, 99, 235, 0.2);
        }

        [data-theme="light"] .frm-in:focus {
            box-shadow:
                inset 5px 5px 10px rgba(163, 177, 198, 0.5),
                inset -5px -5px 10px rgba(255, 255, 255, 0.9),
                0 0 0 3px rgba(37, 99, 235, 0.15);
        }

        .frm-in::placeholder {
            color: var(--text-muted);
            font-weight: 400;
            opacity: 0.7;
        }

        /* Login Button */
        .login-btn {
            width: 100%;
            padding: 18px;
            border: none;
            border-radius: 14px;
            font-size: 14px;
            font-weight: 700;
            cursor: pointer;
            background: var(--mr-gradient-btn);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: var(--transition);
            margin-top: 15px;
            letter-spacing: 1px;
        }

        [data-theme="dark"] .login-btn {
            box-shadow:
                6px 6px 15px rgba(0, 0, 0, 0.4),
                -3px -3px 10px rgba(255, 255, 255, 0.02),
                0 0 25px var(--glow);
        }

        [data-theme="light"] .login-btn {
            box-shadow:
                6px 6px 15px rgba(163, 177, 198, 0.5),
                -6px -6px 15px rgba(255, 255, 255, 0.8),
                0 0 25px var(--glow);
        }

        .login-btn:hover {
            transform: translateY(-3px);
            background: linear-gradient(135deg, #0ea5e9 0%, #2563eb 100%);
        }

        [data-theme="dark"] .login-btn:hover {
            box-shadow:
                8px 8px 20px rgba(0, 0, 0, 0.5),
                -4px -4px 12px rgba(255, 255, 255, 0.02),
                0 0 40px var(--glow);
        }

        [data-theme="light"] .login-btn:hover {
            box-shadow:
                8px 8px 20px rgba(163, 177, 198, 0.6),
                -8px -8px 20px rgba(255, 255, 255, 0.9),
                0 0 40px var(--glow);
        }

        /* Login Footer */
        .login-footer {
            text-align: center;
            margin-top: 30px;
            color: var(--text-muted);
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 1px;
        }

        /* Decorative Elements */
        .login-decor {
            position: fixed;
            border-radius: 50%;
            opacity: 0.3;
            pointer-events: none;
        }

        [data-theme="dark"] .login-decor {
            background: radial-gradient(circle, rgba(37, 99, 235, 0.3) 0%, transparent 70%);
        }

        [data-theme="light"] .login-decor {
            background: radial-gradient(circle, rgba(37, 99, 235, 0.15) 0%, transparent 70%);
        }

        .login-decor-1 {
            width: 400px;
            height: 400px;
            top: -100px;
            right: -100px;
        }

        .login-decor-2 {
            width: 300px;
            height: 300px;
            bottom: -50px;
            left: -50px;
        }

        /* Responsive */
        @media (max-width: 480px) {
            .login-box {
                padding: 35px 25px;
            }

            .login-icon {
                width: 70px;
                height: 70px;
                font-size: 30px;
            }

            .login-title {
                font-size: 18px;
            }
        }
    </style>
</head>
<body data-theme="<?= htmlspecialchars($currentTheme) ?>">
    <!-- Decorative Elements -->
    <div class="login-decor login-decor-1"></div>
    <div class="login-decor login-decor-2"></div>

    <div class="login-container">
        <!-- Theme Toggle -->
        <div class="theme-toggle-login">
            <button type="button" class="theme-btn-login" id="lightThemeBtn" onclick="setTheme('light')" title="Açık Tema">
                <i class="fas fa-sun"></i>
            </button>
            <button type="button" class="theme-btn-login" id="darkThemeBtn" onclick="setTheme('dark')" title="Koyu Tema">
                <i class="fas fa-moon"></i>
            </button>
        </div>

        <div class="login-box">
            <div class="login-icon">
                <i class="fas fa-building"></i>
            </div>
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
        function setTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            document.body.setAttribute('data-theme', theme);
            document.cookie = 'mr_theme=' + theme + ';path=/;max-age=31536000;SameSite=Lax';
            updateThemeButtons(theme);
        }

        function updateThemeButtons(theme) {
            var lightBtn = document.getElementById('lightThemeBtn');
            var darkBtn = document.getElementById('darkThemeBtn');

            if (theme === 'light') {
                lightBtn.classList.add('active');
                darkBtn.classList.remove('active');
            } else {
                darkBtn.classList.add('active');
                lightBtn.classList.remove('active');
            }
        }

        // Initialize theme on load
        document.addEventListener('DOMContentLoaded', function() {
            var theme = document.documentElement.getAttribute('data-theme') || 'light';
            updateThemeButtons(theme);
        });
    </script>
</body>
</html>
