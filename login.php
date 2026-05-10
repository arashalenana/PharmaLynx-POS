<?php
session_start();
require_once 'config/db.php';
require_once 'config/paths.php';

// Set timezone to EAT
date_default_timezone_set('Africa/Nairobi');

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    try {
        $stmt = $conn->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && $password === $user['password']) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header("Location: index.php");
            exit();
        } else {
            $error = "Invalid username or password";
        }
    } catch (PDOException $e) {
        $error = "Database error: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
    <title>Login - PharmaLynx POS</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo $asset_path; ?>images/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo $asset_path; ?>images/favicon-16x16.png">
    <link rel="shortcut icon" href="<?php echo $asset_path; ?>images/favicon.png">

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">

    <!-- PWA: Manifest, meta tags, and service worker registration -->
    <?php include dirname(__FILE__) . '/includes/pwa.php'; ?>

    <style>
        :root {
            --very-black: #000000;
            --dark-gray: #1a1a1a;
            --accent-red: #8b0000;
            --accent-red-hover: #a50000;
        }
        body, html {
            height: 100%;
            margin: 0;
            background-color: var(--very-black);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }
        .login-box {
            width: 100%;
            max-width: 400px;
            background: var(--dark-gray);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.8);
            border: 1px solid #333;
        }
        .login-logo {
            max-width: 120px;
            margin-bottom: 20px;
        }
        .btn-login {
            background-color: var(--accent-red);
            border: none;
            color: white;
            padding: 12px;
            font-weight: bold;
            transition: 0.3s;
        }
        .btn-login:hover {
            background-color: var(--accent-red-hover);
            color: white;
        }
        .form-control {
            background-color: #222;
            border: 1px solid #444;
            color: #fff;
        }
        .form-control:focus {
            background-color: #222;
            border-color: var(--accent-red);
            color: #fff;
            box-shadow: 0 0 0 0.25rem rgba(139, 0, 0, 0.25);
        }
        .input-group-text {
            background-color: #333;
            border: 1px solid #444;
            color: #aaa;
        }
        .form-label {
            color: #ddd;
        }
        .text-muted {
            color: #888 !important;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-box">
            <div class="text-center">
                <img src="<?php echo $asset_path; ?>images/logo_original.png" alt="PharmaLynx Logo" class="login-logo">
                <h4 style="color: var(--accent-red); font-weight: 800; letter-spacing: 1px;">PHARMALYNX</h4>
                <p class="text-muted mb-4">Pharmacy POS System</p>
            </div>

            <?php if ($error): ?>
                <div class="alert alert-danger py-2 small text-center"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold">Username</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" name="username" class="form-control" placeholder="Enter username" required autocomplete="username">
                    </div>
                </div>
                <div class="mb-4">
                    <label class="form-label small fw-bold">Password</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="fas fa-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="Enter password" required autocomplete="current-password">
                    </div>
                </div>
                <button type="submit" class="btn btn-login w-100 rounded-3">SIGN IN</button>
            </form>

            <div class="mt-4 text-center border-top border-secondary pt-3">
                <p class="text-muted small mb-0">Admin: admin / admin123</p>
                <p class="text-muted small">Staff: staff / staff123</p>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
