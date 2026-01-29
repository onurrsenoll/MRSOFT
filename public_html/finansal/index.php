<?php
/**
 * MR HASAR DANISMANLIK - Ana Giris Sayfasi
 * HERZAMAN FARKEDER
 *
 * Login islemleri dogrudan bu dosyada yapilir
 */

// Oturum baslat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Hata raporlama
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Zaman dilimi
date_default_timezone_set('Europe/Istanbul');

// Veritabani ayarlari
define('DB_HOST', 'localhost');
define('DB_NAME', 'mrhasard_mrhasar_db');
define('DB_USER', 'mrhasard_mrhasar_db');
define('DB_PASS', 'Bx&jsqf.55');
define('DB_CHARSET', 'utf8mb4');

// Uygulama ayarlari
define('APP_NAME', 'MR HASAR DANISMANLIK VE FILO YONETIMI');
define('APP_SLOGAN', 'HERZAMAN FARKEDER');
define('APP_VERSION', 'V2.0.0');

// Veritabani baglantisi
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
        PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
    ];
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
} catch (PDOException $e) {
    die("Veritabani baglanti hatasi: " . $e->getMessage());
}

// Yardimci fonksiyonlar
function clean($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function e($string) {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

// Zaten giris yapmissa admin paneline yonlendir
if (isLoggedIn()) {
    header('Location: admin/index.php');
    exit;
}

$error = '';
$username = '';

// Form gonderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Kullanici adi ve sifre gereklidir.';
    } else {
        // Kullanici kontrolu
        $stmt = $pdo->prepare("SELECT * FROM users WHERE (username = ? OR email = ?) AND is_active = 1");
        $stmt->execute([$username, $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Oturum bilgilerini kaydet
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['user_permissions'] = json_decode($user['permissions'] ?? '[]', true);

            // Son giris tarihini guncelle
            $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);

            // Admin paneline yonlendir
            header('Location: admin/index.php');
            exit;
        } else {
            $error = 'Gecersiz kullanici adi veya sifre.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Giris - <?= APP_NAME ?></title>
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
            background: #0a1628;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            padding-right: 5%;
            position: relative;
            overflow: hidden;
        }

        /* Arka plan gorseli - tam ekran, orantili */
        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: url('img/login-bg.jpg') no-repeat center center;
            background-size: contain;
            z-index: 0;
        }

        .login-wrapper {
            width: 100%;
            max-width: 350px;
            padding: 20px;
            position: relative;
            z-index: 1;
        }

        /* Tamamen seffaf modul */
        .login-form {
            background: transparent;
            padding: 40px 30px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            color: #fff;
            font-size: 0.9rem;
            margin-bottom: 8px;
            font-weight: 600;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
        }

        .form-group .input-wrapper {
            position: relative;
        }

        .form-group .input-wrapper i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: rgba(255, 255, 255, 0.6);
            font-size: 1.1rem;
            z-index: 2;
            transition: all 0.3s ease;
        }

        /* Input - seffaf, yazildiginda beyaz dolar */
        .form-group input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            background: transparent;
            border: 2px solid rgba(255, 255, 255, 0.5);
            border-radius: 12px;
            color: #fff;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        /* Yazildiginda beyaz arka plan */
        .form-group input:focus,
        .form-group input:not(:placeholder-shown) {
            background: rgba(255, 255, 255, 0.95);
            color: #1a1a2e;
            border-color: #fff;
            outline: none;
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.3);
        }

        .form-group input:focus::placeholder {
            color: rgba(0, 0, 0, 0.4);
        }

        /* Yazildiginda icon rengi degissin */
        .form-group input:focus ~ .input-wrapper i,
        .form-group input:not(:placeholder-shown) + i {
            color: #1a1a2e;
        }

        .form-group .input-wrapper:has(input:focus) i,
        .form-group .input-wrapper:has(input:not(:placeholder-shown)) i {
            color: #1a1a2e;
        }

        .form-check {
            margin-bottom: 30px;
        }

        .form-check-label {
            color: #fff;
            font-size: 0.9rem;
            text-shadow: 0 2px 4px rgba(0,0,0,0.5);
        }

        .form-check-input {
            background: transparent;
            border: 2px solid rgba(255, 255, 255, 0.5);
        }

        .form-check-input:checked {
            background: #fff;
            border-color: #fff;
        }

        /* Holografik 3D Giris Butonu */
        .btn-login {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 12px;
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
            box-shadow:
                0 0 20px rgba(0, 212, 255, 0.3),
                0 0 40px rgba(0, 212, 255, 0.2),
                inset 0 0 20px rgba(0, 212, 255, 0.1);
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
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.2),
                transparent
            );
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
            border-radius: 14px;
            z-index: -1;
            animation: borderGlow 3s ease-in-out infinite;
            opacity: 0.7;
        }

        .btn-login:hover {
            transform: translateY(-3px) scale(1.02);
            box-shadow:
                0 0 30px rgba(0, 212, 255, 0.5),
                0 0 60px rgba(0, 212, 255, 0.3),
                0 10px 40px rgba(0, 0, 0, 0.3),
                inset 0 0 30px rgba(0, 212, 255, 0.2);
        }

        .btn-login:active {
            transform: translateY(-1px) scale(0.98);
        }

        .btn-login i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        @keyframes holographic {
            0%, 100% {
                background-position: 0% 50%;
            }
            50% {
                background-position: 100% 50%;
            }
        }

        @keyframes shine {
            0% {
                left: -100%;
            }
            50%, 100% {
                left: 100%;
            }
        }

        @keyframes borderGlow {
            0%, 100% {
                background-position: 0% 50%;
                opacity: 0.5;
            }
            50% {
                background-position: 100% 50%;
                opacity: 0.8;
            }
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

        /* 3D Icon efekti */
        .login-icon {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-icon .icon-3d {
            width: 90px;
            height: 90px;
            margin: 0 auto;
            background: transparent;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            border: 2px solid rgba(255, 255, 255, 0.5);
            animation: pulse3d 2s ease-in-out infinite;
        }

        .login-icon .icon-3d i {
            font-size: 2.8rem;
            color: #fff;
            text-shadow: 0 0 20px rgba(255,255,255,0.5);
        }

        @keyframes pulse3d {
            0%, 100% {
                transform: scale(1);
                box-shadow: 0 0 20px rgba(255, 255, 255, 0.2);
            }
            50% {
                transform: scale(1.05);
                box-shadow: 0 0 40px rgba(255, 255, 255, 0.4);
            }
        }

        /* Responsive */
        @media (max-width: 768px) {
            body {
                justify-content: center;
                padding: 20px;
            }

            body::before {
                background-size: cover;
            }

            .login-wrapper {
                max-width: 100%;
            }

            .login-form {
                padding: 30px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="login-wrapper">
        <div class="login-form">
            <!-- 3D Holografik Icon -->
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
                    <label>Kullanici Adi</label>
                    <div class="input-wrapper">
                        <i class="bi bi-person"></i>
                        <input type="text" name="username" placeholder="Kullanici adinizi girin"
                               value="<?= e($username) ?>" required autofocus>
                    </div>
                </div>

                <div class="form-group">
                    <label>Sifre</label>
                    <div class="input-wrapper">
                        <i class="bi bi-lock"></i>
                        <input type="password" name="password" placeholder="Sifrenizi girin" required>
                    </div>
                </div>

                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Beni hatirla</label>
                </div>

                <button type="submit" class="btn-login">
                    <i class="bi bi-box-arrow-in-right"></i>
                    Giris Yap
                </button>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
