<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

redirectIfLoggedIn();

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email    = clean($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (empty($email) || empty($password)) {
        $error = 'Email and password are required.';
    } else {
        $stmt = $conn->prepare('SELECT * FROM users WHERE email = ? LIMIT 1');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $user = $stmt->get_result()->fetch_assoc();

        if ($user && password_verify($password, $user['password'])) {
            startSession($user);
            header('Location: index.php');
            exit();
        } else {
            $error = 'Invalid email or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | LibraryMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; min-height: 100vh; display: flex; background: #f4f6fb; }

        .login-left {
            width: 420px; display: flex; flex-direction: column;
            justify-content: center; padding: 3rem 2.5rem;
            background: linear-gradient(160deg, #12213a 0%, #1e3a5f 100%);
        }
        .brand { display: flex; align-items: center; gap: 12px; margin-bottom: 2.5rem; }
        .brand i { font-size: 2rem; color: #5fa8d3; }
        .brand-name { font-family: 'DM Serif Display', serif; font-size: 1.6rem; color: #fff; }

        .book-shelf { display: flex; align-items: flex-end; gap: 6px; margin-bottom: 2rem; position: relative; padding-bottom: 8px; }
        .book {
            border-radius: 3px 6px 6px 3px;
            display: flex; align-items: center; justify-content: center;
            writing-mode: vertical-rl; text-orientation: mixed;
            font-size: 9px; font-weight: 600; color: rgba(255,255,255,0.85);
            letter-spacing: 0.05em; padding: 8px 4px; cursor: default;
            transition: transform 0.2s;
        }
        .book:hover { transform: translateY(-6px); }
        .b1 { width: 32px; height: 110px; background: #c0392b; }
        .b2 { width: 28px; height: 130px; background: #2980b9; }
        .b3 { width: 34px; height: 95px;  background: #8e44ad; }
        .b4 { width: 30px; height: 120px; background: #27ae60; }
        .b5 { width: 36px; height: 140px; background: #e67e22; }
        .b6 { width: 28px; height: 105px; background: #16a085; }
        .shelf-base {
            position: absolute; bottom: 0; left: -8px; right: -8px;
            height: 6px; background: #8B6914; border-radius: 2px;
        }

        .hero-text { margin-bottom: 2rem; }
        .hero-text h2 {
            font-family: 'DM Serif Display', serif; color: #fff;
            font-size: 1.3rem; line-height: 1.5; margin-bottom: 0.5rem;
            font-style: italic;
        }
        .hero-text p { color: #5fa8d3; font-size: 0.85rem; }

        .stats-row { display: flex; gap: 1rem; }
        .stat-item { flex: 1; text-align: center; background: rgba(255,255,255,0.06); border-radius: 10px; padding: 0.75rem 0.5rem; }
        .stat-num { font-size: 1.3rem; font-weight: 600; color: #fff; }
        .stat-lbl { font-size: 0.72rem; color: #a8bdd4; margin-top: 2px; }

        .login-right { flex: 1; display: flex; align-items: center; justify-content: center; padding: 2rem; }
        .login-box { width: 100%; max-width: 400px; background: #fff; border-radius: 16px; padding: 2.5rem; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .login-box h3 { font-weight: 600; font-size: 1.3rem; margin-bottom: 0.25rem; }
        .login-box p { color: #6b7280; font-size: 0.875rem; margin-bottom: 1.75rem; }
        label { font-size: 0.82rem; font-weight: 500; margin-bottom: 4px; display: block; }
        .form-control { border-radius: 8px; border: 1px solid #e5e9f0; padding: 0.6rem 0.85rem; font-size: 0.875rem; width: 100%; }
        .form-control:focus { border-color: #2c5f8a; box-shadow: 0 0 0 3px rgba(44,95,138,0.1); outline: none; }
        .btn-login { background: #2c5f8a; color: #fff; border: none; width: 100%; padding: 0.7rem; border-radius: 8px; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.2s; }
        .btn-login:hover { background: #1a3f5c; }
        .error-box { background: #fdecea; color: #e74c3c; border-left: 4px solid #e74c3c; padding: 0.65rem 0.9rem; border-radius: 6px; font-size: 0.84rem; margin-bottom: 1rem; }
        .input-wrap { position: relative; }
        .input-wrap i { position: absolute; right: 0.8rem; top: 50%; transform: translateY(-50%); color: #9ca3af; cursor: pointer; }
        .link-register { display: block; text-align: center; margin-top: 1.25rem; color: #2c5f8a; font-size: 0.875rem; text-decoration: none; }
        .link-register:hover { text-decoration: underline; }
        @media (max-width: 768px) { .login-left { display: none; } }
    </style>
</head>
<body>

<div class="login-left">
    <div class="brand"><i class="bi bi-book-half"></i><span class="brand-name">LibraryMS</span></div>

    <div class="book-shelf">
        <div class="book b1"><span>1984</span></div>
        <div class="book b2"><span>Kar</span></div>
        <div class="book b3"><span>Aşk</span></div>
        <div class="book b4"><span>Sapiens</span></div>
        <div class="book b5"><span>İnce Memed</span></div>
        <div class="book b6"><span>Pinhan</span></div>
        <div class="shelf-base"></div>
    </div>

    <div class="hero-text">
        <h2>"A reader lives a thousand lives before he dies."</h2>
        <p>— George R.R. Martin</p>
    </div>

    <div class="stats-row">
        <div class="stat-item"><div class="stat-num">500+</div><div class="stat-lbl">Books</div></div>
        <div class="stat-item"><div class="stat-num">200+</div><div class="stat-lbl">Members</div></div>
        <div class="stat-item"><div class="stat-num">1000+</div><div class="stat-lbl">Borrows</div></div>
    </div>
</div>

<div class="login-right">
    <div class="login-box">
        <h3>Welcome Back</h3>
        <p>Sign in to your account to continue.</p>

        <?php if ($error): ?>
            <div class="error-box"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="mb-3">
                <label for="email">Email Address</label>
                <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com"
                       value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
            </div>
            <div class="mb-4">
                <label for="password">Password</label>
                <div class="input-wrap">
                    <input type="password" id="password" name="password" class="form-control" placeholder="••••••••" required>
                    <i class="bi bi-eye" id="eyeIcon" onclick="togglePassword()"></i>
                </div>
            </div>
            <button type="submit" class="btn-login">Sign In</button>
        </form>

        <a href="register.php" class="link-register">Don't have an account? Register</a>
    </div>
</div>

<script>
function togglePassword() {
    const inp = document.getElementById('password');
    const ico = document.getElementById('eyeIcon');
    inp.type = inp.type === 'password' ? 'text' : 'password';
    ico.className = inp.type === 'password' ? 'bi bi-eye' : 'bi bi-eye-slash';
}
</script>
</body>
</html>
