<?php
/**
 * SAY HASAR DANIŞMANLIK - GİRİŞ SAYFASI
 * MÜKEMMELLİK BİZİ HER ZAMAN AYIRT EDER
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
            
            if ($user && password_verify($password, $user['password_hash'])) {
                // Giriş başarılı
                session_regenerate_id(true);
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            text-transform: uppercase;
        }
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #0a1628, #0d1829, #1a1a2e);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-box {
            background: #111d35;
            border: 1px solid #2a3f5f;
            border-radius: 16px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.5);
        }
        .login-icon {
            width: 80px;
            height: 80px;
            border-radius: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            margin: 0 auto 20px;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            color: #fff;
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.4);
        }
        .login-title {
            text-align: center;
            font-size: 20px;
            font-weight: 800;
            background: linear-gradient(135deg, #3b82f6, #10b981);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 8px;
        }
        .login-slogan {
            text-align: center;
            font-size: 9px;
            color: #f59e0b;
            margin-bottom: 30px;
            letter-spacing: 2px;
            font-weight: 600;
        }
        .alert {
            padding: 14px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 11px;
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid #ef4444;
            color: #ef4444;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .frm-grp {
            margin-bottom: 18px;
        }
        .frm-lbl {
            display: block;
            font-size: 10px;
            color: #94a3b8;
            margin-bottom: 8px;
            font-weight: 600;
        }
        .frm-in {
            width: 100%;
            padding: 14px 16px;
            background: #1a2942;
            border: 2px solid #2a3f5f;
            border-radius: 10px;
            color: #fff;
            font-size: 13px;
            transition: all 0.3s;
        }
        .frm-in:focus {
            outline: none;
            border-color: #3b82f6;
            box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.2);
        }
        .login-btn {
            width: 100%;
            padding: 16px;
            border: none;
            border-radius: 12px;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            background: linear-gradient(135deg, #3b82f6, #8b5cf6);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
            margin-top: 10px;
        }
        .login-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 35px rgba(59, 130, 246, 0.4);
        }
        .login-footer {
            text-align: center;
            margin-top: 25px;
            color: #64748b;
            font-size: 10px;
        }
    </style>
</head>
<body>
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
                <input type="text" name="username" class="frm-in" required autofocus placeholder="KULLANICI ADINIZI GİRİN">
            </div>
            <div class="frm-grp">
                <label class="frm-lbl">
                    <i class="fas fa-lock"></i> ŞİFRE
                </label>
                <input type="password" name="password" class="frm-in" required placeholder="ŞİFRENİZİ GİRİN">
            </div>
            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i> GİRİŞ YAP
            </button>
        </form>
        
        <div class="login-footer">
            V<?= APP_VERSION ?> © <?= date('Y') ?>
        </div>
    </div>
</body>
</html>