<?php //send_notification.php
session_start();
require_once 'db.php';

// Подключаем PHPMailer
require 'PHPMailer/Exception.php';
require 'PHPMailer/PHPMailer.php';
require 'PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    
    $user = getCurrentUser($pdo);
    if ($user['role'] !== 'admin') die("Доступ запрещен");

    $messageTemplate = trim($_POST['message']);
    $clients = $_POST['clients'] ?? []; 
    $settings = getSettings($pdo);

    if (empty($clients) || empty($messageTemplate)) {
        echo "<script>localStorage.setItem('flashToast', JSON.stringify({msg: 'Ошибка: выберите клиентов и введите текст!', isError: true})); window.location.href = document.referrer;</script>";
        exit;
    }

    $sentCount = 0;

    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        
        $mail->Username   = 'zapolskaaveta@gmail.com'; 
        $mail->Password   = 'asjpytxeyhnpuhcr'; 
        
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
        $mail->Port       = 465;
        $mail->CharSet    = 'UTF-8';
        
        $mail->SMTPOptions = array('ssl' => array('verify_peer' => false, 'verify_peer_name' => false, 'allow_self_signed' => true));

        $mail->setFrom('zapolskaaveta@gmail.com', 'Центр психологии J.O.Y.');
        $mail->isHTML(true);
        $mail->Subject = 'Напоминание о записи | J.O.Y.';

        // Прикрепляем логотип для использования внутри HTML письма
        $logoPath = __DIR__ . '/img/Frame.png';
        if (file_exists($logoPath)) {
            $mail->addEmbeddedImage($logoPath, 'joylogo');
        }

        foreach ($clients as $clientVal) {
            $email = null;
            $name = ''; $date = ''; $time = ''; $room = ''; $spec = ''; $topic = '';
            $address = $settings['address'];

            if (strpos($clientVal, 'app_') === 0) {
                $appId = str_replace('app_', '', $clientVal);
                $stmt = $pdo->prepare("SELECT a.*, u.email, s.first_name, s.last_name FROM appointments a LEFT JOIN users u ON a.user_id = u.id LEFT JOIN specialists s ON a.specialist_id = s.id WHERE a.id = ?");
                $stmt->execute([$appId]);
                $data = $stmt->fetch();
                
                if($data) {
                    $email = $data['email']; 
                    $name = $data['guest_name'] ?: 'Клиент'; 
                    $date = !empty($data['appointment_time']) ? date('d.m.Y', strtotime($data['appointment_time'])) : 'Не назначено';
                    $time = !empty($data['appointment_time']) ? date('H:i', strtotime($data['appointment_time'])) : 'Не назначено';
                    $room = !empty($data['room_id']) ? $data['room_id'] : 'Кабинет уточняется';
                    $spec = $data['first_name'] ? $data['first_name'].' '.$data['last_name'] : 'Назначен куратором';
                    $topic = !empty($data['service_type']) ? mb_strtolower($data['service_type']) : 'сессию';
                }
            } 
            elseif (strpos($clientVal, 'grp_') === 0) {
                $pid = str_replace('grp_', '', $clientVal);
                $stmt = $pdo->prepare("SELECT p.*, u.name as guest_name, u.email, g.title, g.event_date, s.first_name, s.last_name FROM group_participants p JOIN users u ON p.user_id = u.id JOIN therapy_groups g ON p.group_id = g.id LEFT JOIN specialists s ON g.spec_id = s.id WHERE p.id = ?");
                $stmt->execute([$pid]);
                $data = $stmt->fetch();
                
                if($data) {
                    $email = $data['email']; 
                    $name = $data['guest_name']; 
                    $date = !empty($data['event_date']) ? date('d.m.Y', strtotime($data['event_date'])) : '';
                    $time = !empty($data['event_date']) ? date('H:i', strtotime($data['event_date'])) : '';
                    $room = 'Групповой зал';
                    $spec = $data['first_name'] ? $data['first_name'].' '.$data['last_name'] : '';
                    $topic = 'групповую терапию: ' . $data['title'];
                }
            }

            $personalizedMessage = str_replace(
                ['{имя}', '{дата}', '{время}', '{кабинет}', '{специалист}', '{услуга}', '{адрес}'],
                [$name, $date, $time, $room, $spec, $topic, $address],
                $messageTemplate
            );

            if (!empty($email)) {
                $mail->clearAddresses(); 
                $mail->addAddress($email);
                
                // КРАСИВЫЙ HTML ШАБЛОН
                $mail->Body = "
                <div style='background-color: #fdfbf9; padding: 40px 20px; font-family: \"Helvetica Neue\", Helvetica, Arial, sans-serif;'>
                    <div style='max-width: 600px; margin: 0 auto; background: #ffffff; border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(224, 198, 173, 0.4); border: 2px solid #E0C6AD;'>
                        
                        <div style='background: #E0C6AD; padding: 25px; text-align: center;'>
                            " . (file_exists($logoPath) ? "<img src='cid:joylogo' alt='J.O.Y. Center' style='max-height: 40px;'>" : "<h1 style='color: white; margin: 0;'>J.O.Y.</h1>") . "
                        </div>
                        
                        <div style='padding: 40px 30px; color: #3D3935; line-height: 1.8; font-size: 16px;'>
                            " . nl2br(htmlspecialchars($personalizedMessage)) . "
                        </div>
                        
                        <div style='background: #fff8f3; padding: 25px; text-align: center; border-top: 1px solid #E0C6AD;'>
                            <p style='margin: 0; font-size: 15px; font-weight: bold; color: #3D3935;'>До встречи в J.O.Y. Center!</p>
                            <p style='margin: 8px 0 0 0; font-size: 12px; color: #aaa;'>Это автоматическое сообщение, пожалуйста, не отвечайте на него.</p>
                        </div>

                    </div>
                </div>";

                if ($mail->send()) {
                    $sentCount++;
                }
            }
        }
    } catch (Exception $e) {
        error_log("Ошибка отправки почты: {$mail->ErrorInfo}");
    }

    echo "<script>localStorage.setItem('flashToast', JSON.stringify({msg: 'Успешно отправлено $sentCount уведомлений клиентам.', isError: false})); window.location.href = document.referrer;</script>";
    exit;
}
?>