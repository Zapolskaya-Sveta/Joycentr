<?php //submit_appointment.php
session_start();
require_once 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $specialist_id = isset($_POST['specialist_id']) ? (int)$_POST['specialist_id'] : null;
    $slot_id = !empty($_POST['slot_id']) ? (int)$_POST['slot_id'] : null;

$service_type = $_POST['service_type'] ?? 'Очная сессия';
    $raw_topic = trim($_POST['topic'] ?? 'Общий вопрос');
    
    // Склеиваем формат услуги и тему (чтобы не менять структуру БД)
    $topic = "[$service_type] " . $raw_topic;
    $age = trim($_POST['age'] ?? 'Не указан');
    $request_text = trim($_POST['request'] ?? '');
    // Контакт убрали, ставим по умолчанию telegram
    $contact = 'telegram'; 
    $phone = trim($_POST['phone'] ?? '');
    $name = trim($_POST['name'] ?? 'Аноним');
    
    $userId = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
    
    try {
        $pdo->beginTransaction();

       
        $service_type = trim($_POST['service_type'] ?? 'Очная индивидуальная сессия');
        
        // 1. Создаем заявку (статус new - ждет одобрения)
        $sql = "INSERT INTO appointments (user_id, specialist_id, guest_name, guest_phone, topic, age, request_text, contact_method, service_type) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$userId, $specialist_id, $name, $phone, $topic, $age, $request_text, $contact, $service_type]);
        $new_appointment_id = $pdo->lastInsertId();

        // 2. Если клиент выбрал время
        if ($slot_id) {
            $checkSlot = $pdo->prepare("SELECT slot_datetime FROM schedule WHERE id = ? AND is_booked = 0");
            $checkSlot->execute([$slot_id]);
            $slotData = $checkSlot->fetch();

            if ($slotData) {
                // Закрываем слот, привязываем заявку
                $updateSlot = $pdo->prepare("UPDATE schedule SET is_booked = 1, appointment_id = ? WHERE id = ?");
                $updateSlot->execute([$new_appointment_id, $slot_id]);

                // Прописываем время в заявку
                $updateApp = $pdo->prepare("UPDATE appointments SET appointment_time = ? WHERE id = ?");
                $updateApp->execute([$slotData['slot_datetime'], $new_appointment_id]);
            }
        }

        $pdo->commit();
        
        $redirectUrl = $_SERVER['HTTP_REFERER'] ?? 'index.php';
        $redirectUrl .= (parse_url($redirectUrl, PHP_URL_QUERY) ? '&' : '?') . 'appoint_success=1';
        header("Location: $redirectUrl");
        exit;
              
    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Ошибка при сохранении заявки: " . $e->getMessage());
    }
} else {
    header("Location: index.php");
    exit;
}
?>