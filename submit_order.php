<?php //submit_order.php
require_once 'db.php';
require_once 'PHPMailer/Exception.php';
require_once 'PHPMailer/PHPMailer.php';
require_once 'PHPMailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$user = getCurrentUser($pdo);
if (!$user) { header("Location: index.php"); exit; }

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['cart_data'])) {
    
    $cartItems = json_decode($_POST['cart_data'], true);
    if (empty($cartItems)) { header("Location: cabinet.php"); exit; }

    $totalPrice = 0;
    foreach ($cartItems as $item) $totalPrice += (int)$item['price'];

    try {
        $pdo->beginTransaction();

        // 1. Создаем заказ (статус по умолчанию pending подтянется из базы)
        $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_price) VALUES (?, ?)");
        $stmt->execute([$user['id'], $totalPrice]);
        $orderId = $pdo->lastInsertId();

        // 2. Добавляем товары в заказ
        $stmtItem = $pdo->prepare("INSERT INTO order_items (order_id, product_id, product_title, price) VALUES (?, ?, ?, ?)");
        $itemsHtmlList = ''; // Собираем список для письма
        foreach ($cartItems as $item) {
            $stmtItem->execute([$orderId, $item['id'], $item['title'], (int)$item['price']]);
            $itemsHtmlList .= "<li style='margin-bottom: 5px;'>" . htmlspecialchars($item['title']) . " — <b>" . (int)$item['price'] . " BYN</b></li>";
        }

        $pdo->commit();

        // 3. ОТПРАВКА НА ПОЧТУ (Если стоит галочка)
        if (isset($_POST['send_to_email']) && $_POST['send_to_email'] == '1') {
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
                $mail->addAddress($user['email'], $user['name']);
                $mail->isHTML(true);
                $mail->Subject = 'Ваш заказ №' . $orderId . ' успешно оформлен | J.O.Y.';

                $mail->Body = "
                <div style='background-color: #fdfbf9; padding: 30px; font-family: Arial, sans-serif;'>
                    <div style='max-width: 500px; margin: 0 auto; background: #ffffff; border-radius: 15px; padding: 30px; border: 2px solid #E0C6AD;'>
                        <h2 style='color: #3D3935; text-align: center; border-bottom: 1px solid #eee; padding-bottom: 15px;'>Спасибо за заказ!</h2>
                        <p style='color: #3D3935; font-size: 16px;'>Здравствуйте, <b>{$user['name']}</b>!</p>
                        <p style='color: #3D3935; font-size: 16px;'>Ваш заказ <b>№{$orderId}</b> успешно оплачен и передан в обработку куратору.</p>
                        
                        <div style='background: #fff8f3; padding: 15px; border-radius: 10px; margin: 20px 0;'>
                            <p style='margin: 0 0 10px 0; font-weight: bold; color: #3D3935;'>Состав заказа:</p>
                            <ul style='color: #3D3935; padding-left: 20px; margin: 0;'>
                                {$itemsHtmlList}
                            </ul>
                        </div>
                        
                        <h3 style='color: #3D3935; text-align: right;'>Итого: {$totalPrice} BYN</h3>
                        
                        <p style='color: #666; font-size: 14px; line-height: 1.5; margin-top: 20px;'>
                            Доступ к приобретенным материалам будет открыт в вашем личном кабинете в течение <b>24 часов</b> после проверки платежа.
                        </p>
                    </div>
                </div>";
                $mail->send();
            } catch (Exception $e) {
                // Если почта не отправилась (например, неверный email), заказ все равно сохранится
            }
        }

        header("Location: cabinet.php?order_success=1");
        exit;

    } catch (Exception $e) {
        $pdo->rollBack();
        die("Ошибка: " . $e->getMessage());
    }
} else {
    header("Location: cabinet.php");
}