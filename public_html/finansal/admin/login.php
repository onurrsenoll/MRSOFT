<?php
/**
 * MR HASAR DANIŞMANLIK - Giriş Sayfası
 * HERZAMAN FARKEDER
 */

require_once 'config.php';

// Zaten giriş yapmışsa yönlendir
if (isLoggedIn()) {
    header('Location: index.php');
    exit;
}

$error = '';
$username = '';

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = clean($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error = 'Kullanıcı adı ve şifre gereklidir.';
    } else {
        // Kullanıcı kontrolü
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

            // Son giriş tarihini güncelle
            $stmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$user['id']]);

            // Aktivite log
            logActivity($pdo, 'LOGIN', 'users', $user['id']);

            // Yönlendir
            header('Location: index.php');
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
        :root {
            --primary-color: #1e3a5f;
            --secondary-color: #2c5282;
            --accent-color: #4299e1;
        }

        body {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .login-container {
            background: white;
            border-radius: 20px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            overflow: hidden;
            width: 100%;
            max-width: 420px;
        }

        .login-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: white;
            padding: 40px 30px;
            text-align: center;
        }

        .login-header h1 {
            font-size: 1.8rem;
            font-weight: 700;
            margin-bottom: 5px;
        }

        .login-header p {
            font-size: 0.95rem;
            opacity: 0.9;
            margin: 0;
        }

        .login-body {
            padding: 40px 30px;
        }

        .form-floating {
            margin-bottom: 20px;
        }

        .form-floating > .form-control {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            padding: 1rem 0.75rem;
        }

        .form-floating > .form-control:focus {
            border-color: var(--accent-color);
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.15);
        }

        .form-floating > label {
            padding: 1rem 0.75rem;
        }

        .btn-login {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: 10px;
            padding: 14px;
            font-size: 1rem;
            font-weight: 600;
            width: 100%;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(30, 58, 95, 0.3);
        }

        .alert {
            border-radius: 10px;
            border: none;
        }

        .logo-icon {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }

        .logo-icon i {
            font-size: 2.5rem;
        }

        .form-check-input:checked {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .footer-text {
            text-align: center;
            color: #718096;
            font-size: 0.85rem;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <div class="logo-icon">
                <i class="bi bi-car-front-fill"></i>
            </div>
            <h1><?= APP_NAME ?></h1>
            <p><?= APP_SLOGAN ?></p>
        </div>

        <div class="login-body">
            <?php if ($error): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <?= e($error) ?>
                </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-floating">
                    <input type="text" class="form-control" id="username" name="username"
                           placeholder="Kullanıcı adı" value="<?= e($username) ?>" required autofocus>
                    <label for="username"><i class="bi bi-person me-2"></i>Kullanıcı Adı</label>
                </div>

                <div class="form-floating">
                    <input type="password" class="form-control" id="password" name="password"
                           placeholder="Şifre" required>
                    <label for="password"><i class="bi bi-lock me-2"></i>Şifre</label>
                </div>

                <div class="form-check mb-4">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                    <label class="form-check-label" for="remember">
                        Beni hatırla
                    </label>
                </div>

                <button type="submit" class="btn btn-primary btn-login">
                    <i class="bi bi-box-arrow-in-right me-2"></i>Giriş Yap
                </button>
            </form>

            <p class="footer-text">
                <?= APP_VERSION ?> &copy; <?= date('Y') ?> Tüm hakları saklıdır.
            </p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
