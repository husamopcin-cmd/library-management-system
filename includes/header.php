<?php requireLogin(); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?? SITE_NAME ?> | <?= SITE_NAME ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=DM+Serif+Display&family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
    <link href="css/style.css" rel="stylesheet">
</head>
<body>

<nav class="sidebar d-flex flex-column">
    <div class="sidebar-brand">
        <i class="bi bi-book-half"></i>
        <span><?= SITE_NAME ?></span>
    </div>

    <ul class="sidebar-nav">
        <li><a href="index.php" class="<?= basename($_SERVER['PHP_SELF'])=='index.php'?'active':'' ?>">
            <i class="bi bi-grid-1x2"></i> Dashboard
        </a></li>
        <li class="nav-section">Collection</li>
        <li><a href="books.php" class="<?= basename($_SERVER['PHP_SELF'])=='books.php'?'active':'' ?>">
            <i class="bi bi-book"></i> Books
        </a></li>
        <li><a href="authors.php" class="<?= basename($_SERVER['PHP_SELF'])=='authors.php'?'active':'' ?>">
            <i class="bi bi-person-circle"></i> Authors
        </a></li>
        <li><a href="categories.php" class="<?= basename($_SERVER['PHP_SELF'])=='categories.php'?'active':'' ?>">
            <i class="bi bi-tags"></i> Categories
        </a></li>
        <li class="nav-section">Members & Records</li>
        <li><a href="members.php" class="<?= basename($_SERVER['PHP_SELF'])=='members.php'?'active':'' ?>">
            <i class="bi bi-people"></i> Members
        </a></li>
        <li><a href="borrows.php" class="<?= basename($_SERVER['PHP_SELF'])=='borrows.php'?'active':'' ?>">
            <i class="bi bi-arrow-left-right"></i> Borrow Records
        </a></li>
    </ul>

    <div class="sidebar-footer">
        <div class="user-info">
            <div class="user-avatar"><?= strtoupper(substr($_SESSION['user_name'], 0, 1)) ?></div>
            <div>
                <div class="user-name"><?= clean($_SESSION['user_name']) ?></div>
                <div class="user-role"><?= ucfirst($_SESSION['user_role']) ?></div>
            </div>
        </div>
        <a href="logout.php" class="btn-logout" title="Logout"><i class="bi bi-box-arrow-right"></i></a>
    </div>
</nav>

<div class="main-content">
    <div class="topbar">
        <h1 class="page-title"><?= $page_title ?? 'Dashboard' ?></h1>
        <div class="topbar-actions">
            <?php if(isset($topbar_btn)): ?>
                <a href="<?= $topbar_btn['url'] ?>" class="btn btn-primary-custom">
                    <i class="bi <?= $topbar_btn['icon'] ?>"></i> <?= $topbar_btn['label'] ?>
                </a>
            <?php endif; ?>
        </div>
    </div>
    <div class="content-body">
        <?php showFlash(); ?>
