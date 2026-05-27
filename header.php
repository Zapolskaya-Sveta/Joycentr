<?php //header.php
require_once 'db.php'; 
if (isset($_POST['accept_cookies'])) {
    setcookie('joy_privacy', 'agreed', time() + (86400 * 30), "/");
    header("Location: " . $_SERVER['REQUEST_URI']);
    exit;
}
$user = getCurrentUser($pdo); 
$currentPage = basename($_SERVER['PHP_SELF']);

$bodyClass = '';
if ($currentPage == 'index.php' || $currentPage == '') { $bodyClass = 'home-page'; } 
elseif ($currentPage == 'services.php') { $bodyClass = 'services-page'; } 
elseif ($currentPage == 'catalog.php') { $bodyClass = 'catalog-page'; } 
elseif ($currentPage == 'publications.php') { $bodyClass = 'publications-page'; } 
elseif ($currentPage == 'article.php') { $bodyClass = 'article-page'; } 
elseif ($currentPage == 'cabinet.php') { $bodyClass = 'cabinet-page'; } 
elseif ($currentPage == 'specialists.php') { $bodyClass = 'specialists-page'; } 
elseif ($currentPage == 'contacts.php') { $bodyClass = 'contacts-page'; }
elseif ($currentPage == 'profile.php') { $bodyClass = 'profile-page'; }
elseif ($currentPage == 'privacy.php') { $bodyClass = 'privacy-page'; }
elseif ($currentPage == 'faq.php') { $bodyClass = 'faq-page'; }
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($pageTitle) ? $pageTitle : 'J.O.Y. Центр психологии' ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Tenor+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="joy.css?v=<?= time() ?>">
    <script src="https://unpkg.com/imask@7.1.3/dist/imask.min.js"></script>
    <script>const isLoggedIn = <?= $user ? 'true' : 'false' ?>;</script>
</head>
<body class="<?= $bodyClass ?>">

<!-- НАВИГАЦИЯ -->
<div class="nav-container">
    <div class="address d-none d-lg-block"><a href="#"><i class="fa fa-map-marker"></i> г. Минск, пр-т Победителей, 11</a></div>
    
    <!-- ВИДЖЕТ ПОЛЬЗОВАТЕЛЯ -->
    <div class="user-corner d-none d-lg-flex">
        <?php if($user): ?>
            <a href="cabinet.php" class="user-link-header"><i class="fas fa-user-circle mr-2"></i> <?= htmlspecialchars($user['name']) ?></a>
        <?php else: ?>
            <a href="#" class="user-link-header" onclick="openAuthModal(event)"><i class="fas fa-sign-in-alt mr-2"></i> Войти</a>
        <?php endif; ?>
    </div>

    <div class="nav-bar">
        <a href="index.php" class="logo-link"><img src="img/Frame.svg" alt="Logo" class="logo"></a>
        <div class="nav-links d-none d-lg-flex">
            <a href="specialists.php" class="<?= $currentPage == 'specialists.php' ? 'active-link' : '' ?>">Специалисты</a>
            <a href="catalog.php" class="<?= $currentPage == 'catalog.php' ? 'active-link' : '' ?>">Онлайн-продукты</a>
            <a href="index.php#section-2">О центре</a>
            <a href="services.php" class="<?= $currentPage == 'services.php' ? 'active-link' : '' ?>">Консультации</a>
            <a href="publications.php" class="<?= $currentPage == 'publications.php' || $currentPage == 'article.php' ? 'active-link' : '' ?>">Публикации</a> 
            <a href="contacts.php" class="<?= $currentPage == 'contacts.php' ? 'active-link' : '' ?>">Контакты</a> 
        </div>
        <div class="d-flex align-items-center">
            <div class="social-icons d-none d-md-flex">
                <a href="#"><i class="fab fa-whatsapp"></i></a>
                <a href="#"><i class="fab fa-telegram-plane"></i></a>
                <a href="cabinet.php" title="Корзина">
                    <i class="fas fa-shopping-basket"></i>
                    <span id="cartCount" class="badge cart-count-badge">0</span>
                </a>
            </div>
            <a href="#" class="appointment-button d-none d-md-flex" onclick="openAppointment(event)">Записаться</a>
        </div>
    </div>
</div>

<div class="mobile-menu d-lg-none">
    <div class="hamburger" id="hamburger"><span></span><span></span><span></span></div>
    <div class="mobile-nav" id="mobileNav">
        <div class="mobile-nav-links">
            <a href="specialists.php">Специалисты</a>
            <a href="catalog.php">Онлайн-продукты</a>
            <a href="index.php#section-2">О центре</a>
            <a href="services.php">Консультации</a>
            <a href="publications.php">Публикации</a>
            <a href="contacts.php">Контакты</a>
            <a href="#" class="mobile-appointment-button" onclick="openAppointment(event)">Записаться</a>
            <a href="cabinet.php"><?= $user ? 'Личный кабинет ('.htmlspecialchars($user['name']).')' : 'Войти / Регистрация' ?></a>
        </div>
    </div>
</div>