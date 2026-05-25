<?php
require_once 'includes/db.php';
require_once 'includes/auth.php';

redirectIfLoggedIn();

$error   = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $full_name = clean($_POST['full_name'] ?? '');
    $email     = clean($_POST['email'] ?? '');
    $password  = $_POST['password'] ?? '';
    $password2 = $_POST['password2'] ?? '';

    if (empty($full_name) || empty($email) || empty($password)) {
        $error = 'All fields are required.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } elseif (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $password2) {
        $error = 'Passwords do not match.';
    } else {
        $stmt = $conn->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->bind_param('s', $email);
        $stmt->execute();
        if ($stmt->get_result()->num_rows > 0) {
            $error = 'This email address is already registered.';
        } else {
            $hash  = password_hash($password, PASSWORD_DEFAULT);
            $stmt2 = $conn->prepare('INSERT INTO users (full_name, email, password) VALUES (?, ?, ?)');
            $stmt2->bind_param('sss', $full_name, $email, $hash);
            if ($stmt2->execute()) {
                $success = 'Registration successful! You can now log in.';
            } else {
                $error = 'An error occurred during registration.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register | LibraryMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <style>
        * { box-sizing: border-box; }
        body { font-family: 'DM Sans', sans-serif; min-height: 100vh; display: flex; align-items: center; justify-content: center; background: #f4f6fb; }
        .register-box { width: 100%; max-width: 440px; background: #fff; border-radius: 16px; padding: 2.5rem; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .brand { display: flex; align-items: center; gap: 10px; margin-bottom: 1.5rem; text-decoration: none; }
        .brand i { font-size: 1.5rem; color: #2c5f8a; }
        .brand span { font-family: 'DM Serif Display', serif; font-size: 1.3rem; color: #12213a; }
        h3 { font-weight: 600; font-size: 1.2rem; margin-bottom: 0.25rem; }
        .sub { color: #6b7280; font-size: 0.875rem; margin-bottom: 1.5rem; }
        label { font-size: 0.82rem; font-weight: 500; margin-bottom: 4px; display: block; }
        .form-control { border-radius: 8px; border: 1px solid #e5e9f0; padding: 0.6rem 0.85rem; font-size: 0.875rem; width: 100%; }
        .form-control:focus { border-color: #2c5f8a; box-shadow: 0 0 0 3px rgba(44,95,138,0.1); outline: none; }
        .btn-register { background: #2c5f8a; color: #fff; border: none; width: 100%; padding: 0.7rem; border-radius: 8px; font-size: 0.9rem; font-weight: 500; cursor: pointer; transition: background 0.2s; }
        .btn-register:hover { background: #1a3f5c; }
        .error-box { background: #fdecea; color: #e74c3c; border-left: 4px solid #e74c3c; padding: 0.65rem 0.9rem; border-radius: 6px; font-size: 0.84rem; margin-bottom: 1rem; }
        .success-box { background: #e8f8ee; color: #27ae60; border-left: 4px solid #27ae60; padding: 0.65rem 0.9rem; border-radius: 6px; font-size: 0.84rem; margin-bottom: 1rem; }
        .strength-bar { height: 4px; border-radius: 2px; margin-top: 6px; background: #e5e9f0; }
        .strength-fill { height: 100%; border-radius: 2px; transition: width 0.3s, background 0.3s; width: 0; }
        .link-login { display: block; text-align: center; margin-top: 1.25rem; color: #2c5f8a; font-size: 0.875rem; text-decoration: none; }
        .link-login:hover { text-decoration: underline; }
    </style>
</head>
<body>
<div class="register-box">
    <a href="login.php" class="brand"><i class="bi bi-book-half"></i><span>LibraryMS</span></a>
    <h3>Create an Account</h3>
    <p class="sub">Register to access the library management system.</p>

    <?php if ($error): ?>
        <div class="error-box"><i class="bi bi-exclamation-circle"></i> <?= htmlspecialchars($error) ?></div>
    <?php endif; ?>
    <?php if ($success): ?>
        <div class="success-box"><i class="bi bi-check-circle"></i> <?= htmlspecialchars($success) ?>
            <a href="login.php">Sign in →</a>
        </div>
    <?php endif; ?>

    <form method="POST" action="">
        <div class="mb-3">
            <label for="full_name">Full Name</label>
            <input type="text" id="full_name" name="full_name" class="form-control" placeholder="John Doe"
                   value="<?= htmlspecialchars($_POST['full_name'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label for="email">Email Address</label>
            <input type="email" id="email" name="email" class="form-control" placeholder="you@example.com"
                   value="<?= htmlspecialchars($_POST['email'] ?? '') ?>" required>
        </div>
        <div class="mb-3">
            <label for="password">Password</label>
            <input type="password" id="password" name="password" class="form-control" placeholder="At least 6 characters" required>
            <div class="strength-bar"><div class="strength-fill" id="strengthFill"></div></div>
        </div>
        <div class="mb-4">
            <label for="password2">Confirm Password</label>
            <input type="password" id="password2" name="password2" class="form-control" placeholder="Repeat your password" required>
        </div>
        <button type="submit" class="btn-register">Register</button>
    </form>
    <a href="login.php" class="link-login">Already have an account? Sign in</a>
</div>
<script>
document.getElementById('password').addEventListener('input', function() {
    const v = this.value;
    let strength = 0;
    if (v.length >= 6) strength++;
    if (/[A-Z]/.test(v)) strength++;
    if (/[0-9]/.test(v)) strength++;
    if (/[^A-Za-z0-9]/.test(v)) strength++;
    const colors = ['#e74c3c','#e67e22','#f1c40f','#27ae60'];
    const fill = document.getElementById('strengthFill');
    fill.style.width = (strength * 25) + '%';
    fill.style.background = colors[strength - 1] || '#e5e9f0';
});
</script>
</body>
</html>
