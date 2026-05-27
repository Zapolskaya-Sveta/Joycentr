<?php //contacts.php
require_once 'db.php';
$settings = getSettings($pdo); // Получаем настройки из БД
$pageTitle = "Контакты | J.O.Y.";
require_once 'header.php';
?>

<!-- Убрал сплошной цвет фона, чтобы было видно твою картинку из CSS -->
<section class="section" style="padding-top: 150px; padding-bottom: 100px; background: transparent;">
    <div class="mandala-wrapper-header">
        <img src="img/Group 186.png" alt="Фон" class="rotating-mandala">
    </div>
    <div class="container">
        
        <!-- ВЕРХНИЙ БЛОК: КОНТАКТЫ И ОБРАТНАЯ СВЯЗЬ -->
        <div class="row mb-5">
            
            <!-- ЛЕВАЯ КОЛОНКА: Контакты -->
            <div class="col-lg-6 mb-5 mb-lg-0 pr-lg-5">
                <h2 class="mb-4 pb-3" style="font-family: 'Tenor Sans', sans-serif; color: #3D3935; font-size: 2.2rem; border-bottom: 2px solid #E0C6AD; display: inline-block; padding-right: 40px;">
                    КОНТАКТЫ
                </h2>
                
                <div class="mt-3">
                    <p style="font-size: 1.8rem; font-family: 'Tenor Sans', sans-serif; color: #3D3935; margin-bottom: 15px;">
                        <?= htmlspecialchars($settings['phone']) ?>
                    </p>

                    <!-- Текстовые ссылки мессенджеров -->
                    <div class="d-flex align-items-center mb-4" style="font-family: 'Lato', sans-serif; font-size: 1.1rem; gap: 20px;">
                        <a href="https://t.me/<?= str_replace('@', '', $settings['telegram']) ?>" class="text-muted" style="text-decoration: none; transition: 0.3s;" onmouseover="this.style.color='#E0C6AD'" onmouseout="this.style.color='#6c757d'">Telegram</a>
                        <a href="mailto:<?= htmlspecialchars($settings['email']) ?>" class="text-muted" style="text-decoration: none; transition: 0.3s;" onmouseover="this.style.color='#E0C6AD'" onmouseout="this.style.color='#6c757d'">Email</a>
                    </div>

                    <!-- Иконки соцсетей -->
                    <div class="d-flex align-items-center mb-4">
                        <a href="<?= htmlspecialchars($settings['instagram']) ?>" class="contact-icon-circle"><i class="fab fa-instagram"></i></a>
                        <a href="https://t.me/<?= str_replace('@', '', $settings['telegram']) ?>" class="contact-icon-circle"><i class="fab fa-telegram-plane"></i></a>
                    </div>

                    <!-- Адрес и время работы -->
                    <p style="font-family: 'Lato', sans-serif; font-size: 1.1rem; color: #3D3935; margin-bottom: 10px;">
                        <?= htmlspecialchars($settings['address']) ?>
                    </p>
                    <p class="text-muted" style="font-family: 'Lato', sans-serif; font-size: 1rem;">
                        Время работы: <?= htmlspecialchars($settings['work_hours']) ?>
                    </p>
                </div>
            </div>

            <!-- ПРАВАЯ КОЛОНКА: Обратная связь -->
            <div class="col-lg-6">
                <h2 class="mb-4 pb-3" style="font-family: 'Tenor Sans', sans-serif; color: #3D3935; font-size: 2.2rem; border-bottom: 2px solid #E0C6AD; display: inline-block; padding-right: 40px;">
                    ОБРАТНАЯ СВЯЗЬ
                </h2>

                <div class="mt-3">
                    <p class="text-muted mb-4" style="font-family: 'Lato', sans-serif; font-size: 1.1rem; line-height: 1.6;">
                        Остались вопросы? Не можете определиться с выбором специалиста или формата терапии? Наш куратор готов помочь вам разобраться во всех деталях.
                    </p>

                    <!-- Аккуратная кнопка (убрал w-100, задал точные отступы) -->
                    <button class="main-button" style="padding: 0.9rem 2.5rem; font-size: 0.95rem; max-width: 300px;" onclick="document.getElementById('callbackModal').style.display='flex'; event.preventDefault();">
                        ОБРАТНАЯ СВЯЗЬ
                    </button>

                    <p class="small text-muted mt-4" style="font-family: 'Lato', sans-serif;">
                        Нажимая на кнопку, вы соглашаетесь с <a href="privacy.php" style="color: #E0C6AD; text-decoration: underline;">политикой конфиденциальности</a>.
                    </p>
                </div>
            </div>

        </div>

        <!-- НИЖНИЙ БЛОК: КАРТА НА ВСЮ ШИРИНУ -->
        <div class="row">
            <div class="col-12">
                <div class="map-container" style="border-radius: 20px; overflow: hidden; box-shadow: 0 10px 30px rgba(224, 198, 173, 0.4); height: 500px; border: 2px solid #E0C6AD;">
                    <iframe src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d2350.29777931326!2d27.54807491117172!3d53.90871143242205!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x46dbcfec9920cc83%3A0xc4f95d85dc410978!2z0L_RgC3Rgi4g0J_QvtCx0LXQtNC40YLQtdC70LXQuSAxMSwg0JzQuNC90YHGsQ!5e0!3m2!1sru!2sby!4v1700000000000!5m2!1sru!2sby" width="100%" height="100%" style="border:0;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
                </div>
            </div>
        </div>

    </div>
</section>

<?php require_once 'footer.php'; ?>