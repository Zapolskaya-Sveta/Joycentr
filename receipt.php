<?php //receipt.php
require_once 'db.php';

$user = getCurrentUser($pdo);
if (!$user) { header("Location: index.php"); exit; }

$orderId = isset($_GET['id']) ? (int)$_GET['id'] : 0;

$sql = "SELECT o.*, u.surname, u.name, u.patronymic, u.email 
        FROM orders o 
        JOIN users u ON o.user_id = u.id 
        WHERE o.id = ? AND (o.user_id = ? OR ? = 'admin')"; 

$stmt = $pdo->prepare($sql);
$stmt->execute([$orderId, $user['id'], $user['role']]);
$order = $stmt->fetch();

if (!$order) { die("Заказ не найден или у вас нет прав на его просмотр."); }

$stmtItems = $pdo->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmtItems->execute([$orderId]);
$items = $stmtItems->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Чек №<?= $orderId ?></title>
    <style>
        body { font-family: 'Courier New', Courier, monospace; background: #fdfbf9; padding: 40px; }
        .receipt { max-width: 400px; margin: 0 auto; background: #fff; padding: 30px; box-shadow: 0 10px 30px rgba(224, 198, 173, 0.3); border-radius: 10px; border-top: 5px solid #E0C6AD; }
        .header { text-align: center; margin-bottom: 20px; border-bottom: 1px dashed #E0C6AD; padding-bottom: 10px; }
        .info { margin-bottom: 20px; font-size: 14px; color: #3D3935; }
        .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; font-size: 14px; }
        .items-table th { text-align: left; border-bottom: 1px solid #E0C6AD; padding-bottom: 5px; }
        .items-table td { padding: 8px 0; border-bottom: 1px dashed #eee; }
        .total { text-align: right; font-size: 18px; font-weight: bold; border-top: 1px solid #E0C6AD; padding-top: 15px; color: #3D3935; }
        .footer { text-align: center; margin-top: 30px; font-size: 12px; color: #888; }
        .btn-print { display: block; width: 80%; padding: 12px; margin: 30px auto 0 auto; background: #E0C6AD; border: none; cursor: pointer; text-align: center; text-decoration: none; color: #3D3935; font-weight: bold; border-radius: 25px; transition: 0.3s; }
        .btn-print:hover { background: #d4b79a; }
        @media print { body { background: #fff; padding: 0; } .receipt { box-shadow: none; max-width: 100%; border:none; } .btn-print { display: none; } }
    </style>
</head>
<body>
<div class="receipt">
    <div class="header">
        <h2 style="margin:0;">J.O.Y. Center</h2>
        <p style="margin:5px 0 0 0;">Электронный чек</p>
    </div>
    <div class="info">
        <p><strong>Заказ №:</strong> <?= $order['id'] ?></p>
        <p><strong>Дата:</strong> <?= date('d.m.Y H:i', strtotime($order['created_at'])) ?></p>
        <p><strong>Покупатель:</strong> <?= htmlspecialchars($order['surname'] . ' ' . $order['name']) ?></p>
        <p><strong>Email:</strong> <?= htmlspecialchars($order['email']) ?></p>
    </div>
    <table class="items-table">
        <thead>
            <tr><th>Наименование</th><th style="text-align: right;">Цена</th></tr>
        </thead>
        <tbody>
            <?php foreach ($items as $item): ?>
            <tr>
                <td><?= htmlspecialchars($item['product_title']) ?></td>
                <td style="text-align: right;"><?= number_format($item['price'], 0, '.', ' ') ?> BYN</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <div class="total">ИТОГО: <?= number_format($order['total_price'], 0, '.', ' ') ?> BYN</div>
    <div class="footer">
        <p>Спасибо за покупку!</p>
        <p>Центр психологии J.O.Y.</p>
        <p>г. Минск, пр-т Победителей, 11</p>
    </div>
    <a href="javascript:window.print()" class="btn-print">Скачать / Печать</a>
</div>
</body>
</html>