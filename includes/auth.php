<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function requireLogin() {
    if (!isset($_SESSION['user_id'])) {
        header('Location: login.php');
        exit();
    }
}

function redirectIfLoggedIn() {
    if (isset($_SESSION['user_id'])) {
        header('Location: index.php');
        exit();
    }
}

function startSession($user) {
    $_SESSION['user_id']    = $user['id'];
    $_SESSION['user_name']  = $user['full_name'];
    $_SESSION['user_email'] = $user['email'];
    $_SESSION['user_role']  = $user['role'];
}

function endSession() {
    session_unset();
    session_destroy();
    header('Location: login.php');
    exit();
}

function clean($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function setFlash($message, $type = 'success') {
    $_SESSION['flash'] = ['message' => $message, 'type' => $type];
}

function showFlash() {
    if (isset($_SESSION['flash'])) {
        $f = $_SESSION['flash'];
        $color = $f['type'] === 'success' ? '#27ae60' : ($f['type'] === 'danger' ? '#e74c3c' : '#f39c12');
        echo '<div class="alert-box" style="background:'.$color.'15;border-left:4px solid '.$color.';color:'.$color.';">'
            . htmlspecialchars($f['message']) . '</div>';
        unset($_SESSION['flash']);
    }
}
