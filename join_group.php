<?php //join_group.php
session_start();
require_once 'db.php';

function redirectWithToast($msg, $isError = false) {
    $errStr = $isError ? 'true' : 'false';
    echo "<script>localStorage.setItem('flashToast', JSON.stringify({msg: '$msg', isError: $errStr})); window.location.href = document.referrer;</script>";
    exit;
}

if (!isset($_SESSION['user_id'])) {
    redirectWithToast('Для записи в группу необходимо войти в аккаунт.', true);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['group_id'])) {
    $groupId = (int)$_POST['group_id'];
    $userId = $_SESSION['user_id'];

    // Проверяем, не записан ли уже этот клиент
    $check = $pdo->prepare("SELECT id FROM group_participants WHERE group_id=? AND user_id=?");
    $check->execute([$groupId, $userId]);
    if ($check->fetch()) {
        redirectWithToast('Вы уже находитесь в списке участников этой группы.', true);
    }

    // Получаем максимальное количество мест
    $grp = $pdo->prepare("SELECT max_seats FROM therapy_groups WHERE id=?");
    $grp->execute([$groupId]);
    $maxSeats = $grp->fetchColumn();

    // Считаем текущих активных участников
    $cnt = $pdo->prepare("SELECT COUNT(*) FROM group_participants WHERE group_id=? AND status='active'");
    $cnt->execute([$groupId]);
    $occupied = $cnt->fetchColumn();

    // Если места еще есть - статус active, если нет - waitlist (лист ожидания)
    $status = ($occupied >= $maxSeats) ? 'waitlist' : 'active';

    $pdo->prepare("INSERT INTO group_participants (group_id, user_id, status) VALUES (?, ?, ?)")->execute([$groupId, $userId, $status]);

    if ($status === 'waitlist') {
        redirectWithToast('Мест не осталось. Вы успешно добавлены в лист ожидания!', false);
    } else {
        redirectWithToast('Вы успешно записаны на воркшоп!', false);
    }
} else {
    header("Location: index.php");
}
?>