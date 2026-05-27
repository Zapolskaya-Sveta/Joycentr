<?php //submit_review.php
session_start();
require_once 'db.php';

function redirectWithToast($msg, $isError = false) {
    $errStr = $isError ? 'true' : 'false';
    echo "<script>
        localStorage.setItem('flashToast', JSON.stringify({msg: '$msg', isError: $errStr}));
        window.location.href = document.referrer ? document.referrer : 'index.php';
    </script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    
    $spec_id = (int)$_POST['specialist_id'];
    $rating = (int)$_POST['rating'];
    $text = trim($_POST['review_text']);
    $user_id = $_SESSION['user_id'];

    if ($rating < 1) $rating = 1;
    if ($rating > 5) $rating = 5;

    $checkApp = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE user_id = ? AND specialist_id = ? AND status = 'completed'");
    $checkApp->execute([$user_id, $spec_id]);
    
    if ($checkApp->fetchColumn() == 0) {
        redirectWithToast('Ошибка: Вы не можете оставить отзыв, так как у вас не было сессий с этим психологом.', true);
    }

    if (!empty($text)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO reviews (specialist_id, user_id, rating, review_text, status) VALUES (?, ?, ?, ?, 'pending')");
            $stmt->execute([$spec_id, $user_id, $rating, $text]);
            redirectWithToast('Спасибо! Ваш отзыв отправлен и появится после проверки.', false);
        } catch (PDOException $e) {
            redirectWithToast('Ошибка при сохранении отзыва.', true);
        }
    } else {
        redirectWithToast('Ошибка: Пожалуйста, напишите текст отзыва.', true);
    }
} else {
    redirectWithToast('Оставлять отзывы могут только авторизованные клиенты.', true);
}
?>