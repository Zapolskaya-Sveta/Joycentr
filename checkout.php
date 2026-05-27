<?php //checkout.php
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['cart_data'])) { header("Location: cabinet.php"); exit; }
$cartData = $_POST['cart_data']; $items = json_decode($cartData, true); $total = 0;
foreach($items as $item) $total += (int)$item['price'];
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Оформление заказа | J.O.Y.</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Tenor+Sans&display=swap" rel="stylesheet">
    <style>
        body { background-color: #fdfbf9; font-family: 'Lato', sans-serif; display: flex; align-items: center; justify-content: center; height: 100vh; margin: 0; }
        .checkout-box { background: white; padding: 40px; border-radius: 20px; box-shadow: 0 10px 30px rgba(224, 198, 173, 0.3); width: 100%; max-width: 450px; border-top: 5px solid #E0C6AD; }
        .bank-header { text-align: center; margin-bottom: 30px; border-bottom: 2px solid #fdfbf9; padding-bottom: 20px; }
        .form-control { border-radius: 12px; background: #fff; border: 2px solid #E0C6AD; padding: 10px 15px; box-shadow: none; font-family: 'Lato', sans-serif;}
        .form-control:focus { border-color: #3D3935; box-shadow: none; }
        .pay-btn { background: linear-gradient(to bottom, #FFE9D4, #E0C6AD); color: #3D3935; font-weight: bold; width: 100%; padding: 12px; border-radius: 25px; border: 2px solid #E0C6AD; font-size: 1.1rem; transition: all 0.3s; cursor: pointer; text-transform: uppercase; font-family: 'Lato', sans-serif;}
        .pay-btn:hover { transform: translateY(-2px); box-shadow: 0 6px 15px rgba(224, 198, 173, 0.4); }
        .loader-overlay { position: absolute; top:0; left:0; width:100%; height:100%; background:rgba(253, 251, 249, 0.9); display:none; justify-content:center; align-items:center; flex-direction:column; border-radius: 20px; z-index: 10;}
        .spinner-border { color: #E0C6AD; width: 3rem; height: 3rem; }
    </style>
</head>
<body>

<div class="checkout-box position-relative">
    <div class="loader-overlay" id="loader">
        <div class="spinner-border mb-3" role="status"></div>
        <h5 style="color: #3D3935; font-family: 'Tenor Sans', sans-serif;">Обработка платежа...</h5>
        <p class="text-muted small">Пожалуйста, не закрывайте окно</p>
    </div>

    <div class="bank-header">
        <h4 class="mb-1" style="color: #3D3935; font-family: 'Tenor Sans', sans-serif;">Оплата заказа</h4>
        <div class="text-muted small">Введите реквизиты для оплаты материалов</div>
    </div>

    <div class="mb-4 text-center">
        <span class="text-muted d-block">Итого к оплате:</span>
        <h2 class="font-weight-bold" style="color: #3D3935;"><?= $total ?>.00 BYN</h2>
    </div>

    <form id="paymentForm" action="submit_order.php" method="POST">
        <input type="hidden" name="cart_data" value="<?= htmlspecialchars($cartData) ?>">
        
        <div class="form-group">
            <label class="small font-weight-bold text-muted">Номер карты</label>
            <input type="text" class="form-control" id="card-number" placeholder="0000 0000 0000 0000" required>
        </div>
        <div class="row">
            <div class="col-6 form-group">
                <label class="small font-weight-bold text-muted">Срок действия</label>
                <input type="text" class="form-control text-center" id="card-date" placeholder="ММ/ГГ" required>
            </div>
            <div class="col-6 form-group">
                <label class="small font-weight-bold text-muted">CVC / CVV</label>
                <input type="password" class="form-control text-center" id="card-cvc" placeholder="•••" required>
            </div>
        </div>
        <div class="form-group mb-4">
            <label class="small font-weight-bold text-muted">Имя владельца (латиницей)</label>
            <input type="text" class="form-control text-uppercase" id="card-name" placeholder="" required>
        </div>
        
        <!-- ГАЛОЧКИ: Политика и Отправка на почту -->
        <div class="form-group mb-4">
            <div class="custom-control custom-checkbox mb-2">
                <input type="checkbox" class="custom-control-input" id="privacyCheck" required checked>
                <label class="custom-control-label small text-muted" for="privacyCheck" style="cursor: pointer; line-height: 1.5;">Я согласен с <a href="privacy.php" style="color: #E0C6AD;">политикой конфиденциальности</a></label>
            </div>
            <div class="custom-control custom-checkbox">
                <!-- Это поле (name="send_to_email") мы будем ловить в submit_order.php -->
                <input type="checkbox" class="custom-control-input" id="emailCheck" name="send_to_email" value="1" checked>
                <label class="custom-control-label small text-muted" for="emailCheck" style="cursor: pointer; line-height: 1.5;">Отправить информацию о заказе на Email</label>
            </div>
        </div>
        
        <button type="submit" class="pay-btn mt-2">Оплатить</button>
        <div class="text-center mt-4">
            <a href="catalog.php" class="text-muted small" style="text-decoration: underline;">Отменить и вернуться в каталог</a>
        </div>
    </form>
</div>

<script src="https://unpkg.com/imask@7.1.3/dist/imask.min.js"></script>
<script>
    IMask(document.getElementById('card-number'), { mask: '0000 0000 0000 0000' });
    IMask(document.getElementById('card-date'), { mask: '00/00' });
    IMask(document.getElementById('card-cvc'), { mask: '000' });
    
    document.getElementById('card-name').addEventListener('input', function() {
        this.value = this.value.replace(/[^A-Za-z\s]/g, '');
    });

    document.getElementById('paymentForm').addEventListener('submit', function(e) {
        e.preventDefault();
        document.getElementById('loader').style.display = 'flex';
        setTimeout(() => { this.submit(); }, 2000);
    });
</script>
</body>
</html>