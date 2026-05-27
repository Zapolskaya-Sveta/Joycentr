<?php //footer.php
$settings = getSettings($pdo); 
?>
<section id="section-11" class="section section-11">
    <div class="container footer-container">
        <div class="footer-main">
            <div class="footer-row first-row d-flex justify-content-between align-items-start">
                <div class="footer-left d-flex flex-column">
                    <a href="index.php" class="logo-link mb-3"><img src="img/Frame.svg" alt="Logo" class="logo"></a>
                    <a href="privacy.php" class="legal-link mt-auto">Политика конфиденциальности</a>
                </div>
                
                <div class="footer-nav d-flex justify-content-center align-items-center flex-grow-1 px-3">
                    <a href="index.php#section-2" class="nav-link">О центре</a>
                    <a href="index.php#section-3" class="nav-link">Наш подход</a>
                    <a href="specialists.php" class="nav-link">Специалисты</a>
                    <a href="services.php" class="nav-link">Услуги</a>
                    <a href="faq.php" class="nav-link">FAQ</a>
                    <a href="publications.php" class="nav-link">Публикации</a>
                </div>

                <div class="footer-contacts text-right">
                    <a href="index.php#section-10" class="nav-link p-0 mb-2 d-block text-uppercase" style="font-family: 'Tenor Sans', sans-serif;">КОНТАКТЫ</a>
                    <div class="contact-phone font-weight-bold" style="font-size: 1.2rem;"><?= htmlspecialchars($settings['phone']) ?></div>
                    <div class="contact-address text-muted mb-2" style="font-size: 0.9rem;"><?= htmlspecialchars($settings['address']) ?></div>
                    <div class="contact-question d-flex align-items-center justify-content-end">
                        <span class="mr-2 text-muted" style="font-size: 0.9rem;">Задать вопрос</span>
                        <a href="https://wa.me/" class="social-icon whatsapp"><i class="fab fa-whatsapp"></i></a>
                        <a href="https://t.me/<?= str_replace('@', '', $settings['telegram']) ?>" class="social-icon telegram ml-2"><i class="fab fa-telegram-plane"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="footer-divider"></div>
        <div class="footer-copyright">J.O.Y Wellness space 2026 ©</div>
    </div>
</section>

<!-- =========================================
   МОДАЛЬНЫЕ ОКНА (ФОРМЫ)
========================================= -->

<!-- ФОРМА ЗАПИСИ НА СЕССИЮ -->
<div class="modal-form" id="appointmentModal">
    <form class="form-container" action="submit_appointment.php" method="POST">
        <span class="close-btn" onclick="document.getElementById('appointmentModal').style.display='none'; document.body.style.overflow='';">&times;</span>
        
        <div class="form-title">ЗАПОЛНИТЕ ФОРМУ</div>

        <div class="mb-3">
            <select class="form-select" name="specialist_id" id="appointSpecialistId" required>
                <option value="" disabled selected>Выберите специалиста</option>
                <?php
                // ВЫВОДИМ ТОЛЬКО ФИО
                $stmtSpecs = $pdo->query("SELECT id, first_name, last_name, patronymic, specialization, description FROM specialists");
                while ($sp = $stmtSpecs->fetch()) {
                    $searchStr = mb_strtolower($sp['specialization'] . ' ' . $sp['description']);
                    $fullName = trim($sp['last_name'] . ' ' . $sp['first_name'] . ' ' . ($sp['patronymic'] ?? ''));
                    echo "<option value='{$sp['id']}' data-search='{$searchStr}'>" . htmlspecialchars($fullName) . "</option>";
                }
                ?>
            </select>
        </div>

        <div class="mb-3" id="slotDropdownContainer" style="display: none;">
            <select class="form-select" name="slot_id" id="appointSlotDropdown" required>
                <option value="" disabled selected>Выберите свободное время</option>
            </select>
        </div>

        <div class="mb-3">
            <select class="form-select" name="service_type" id="appointServiceType" onchange="filterSpecialistsByService()" required>
                <option value="Очная индивидуальная сессия">Очная индивидуальная сессия</option>
                <option value="Онлайн сессия">Онлайн сессия</option>
                <option value="Парная терапия">Парная (семейная) терапия</option>
            </select>
        </div>

        <div class="mb-3">
            <select class="form-select" name="topic" id="appointTopic" required>
                <option value="" disabled selected>Что Вас беспокоит?</option>
                <?php
                $stmtTopics = $pdo->query("SELECT title FROM topics ORDER BY id ASC");
                while ($t = $stmtTopics->fetch()) {
                    echo "<option value='" . htmlspecialchars($t['title']) . "'>" . htmlspecialchars($t['title']) . "</option>";
                }
                ?>
            </select>
        </div>
        
        <div class="mb-3">
            <select class="form-select" name="age" id="appointAge" required>
                <option value="" disabled selected>Ваш возраст</option>
                <option value="18-25">18-25</option>
                <option value="26-35">26-35</option>
                <option value="36+">36+</option>
            </select>
        </div>
        
        <div class="mb-3">
            <textarea class="form-control joy-input" name="request" id="appointRequest" rows="4" placeholder="Опишите кратко ваш запрос" required></textarea>
        </div>
        
        <div class="form-title modal-form-subtitle">ВАШИ КОНТАКТЫ</div>
        
        <div class="mb-3 mt-3">
            <input type="text" name="name" class="form-control joy-input" id="appointName" placeholder="Ваше Имя" required>
        </div>
        
        <div class="phone-group-container">
            <select class="country-select-joy" id="countrySelector">
                <option value="by" selected>BY</option>
                <option value="ru">RU</option>
            </select>
            <input type="tel" name="phone" class="form-control joy-input phone-input-joy" id="phoneInput" placeholder="+375 (29) 123-45-67" required>
        </div>
        
        <!-- ИСПРАВЛЕННЫЙ БЛОК ГАЛОЧКИ -->
        <div class="privacy-check-wrapper">
            <input type="checkbox" id="privacyCheck" class="privacy-check-input" required checked>
            <label for="privacyCheck" class="privacy-check-label">Я согласен(на) на обработку персональных данных</label>
        </div>
        
        <button type="submit" class="submit-btn">ЗАПИСАТЬСЯ</button>
        <a href="#" class="back-link" onclick="document.getElementById('appointmentModal').style.display='none'; document.body.style.overflow='';">Отмена</a>
    </form>
</div>

<!-- МЕДИТАЦИИ -->
<div class="modal-form" id="meditationModal">
    <div class="form-container" style="max-width: 1000px; padding: 0;">
        <span class="close-btn" onclick="closeMeditationModal()">&times;</span>
        <section class="meditation-section" style="margin:0; border-radius: 20px;">
            <h2>МЕДИТАЦИИ</h2>
            <p>Получите наиболее популярные медитации на:</p>
            <div class="options" style="display: flex; justify-content: center; gap: 30px; margin: 40px 0; flex-wrap: wrap;">
                <div style="max-width: 200px; text-align: center;">
                    <div class="option" onclick="selectOption(this)" data-id="1" data-price="42"><img src="img/Frame(4).png" alt="Денежный поток"></div>
                    <div style="font-family: 'Lato', sans-serif; font-size: 0.95rem; font-weight: bold;">Медитация<br>"Денежный поток"</div>
                </div>
                <div style="max-width: 200px; text-align: center;">
                    <div class="option" onclick="selectOption(this)" data-id="2" data-price="42"><img src="img/Frame (6).png" alt="Исполнение желаний"></div>
                    <div style="font-family: 'Lato', sans-serif; font-size: 0.95rem; font-weight: bold;">Медитация<br>"Исполнение желаний"</div>
                </div>
                <div style="max-width: 200px; text-align: center;">
                    <div class="option" onclick="selectOption(this)" data-id="4" data-price="50"><img src="img/Group 194.png" alt="Активация сердца"></div>
                    <div style="font-family: 'Lato', sans-serif; font-size: 0.95rem; font-weight: bold;">Активация сердечного центра</div>
                </div>
            </div>
            <button class="submit-btn" style="max-width: 300px; margin: 0 auto;" onclick="addSelectedMeditationToCart()">Выбрать</button>
            <div class="description" style="font-size: 0.95rem; line-height: 1.6; color: #666; max-width: 800px; margin: 30px auto; border-top: 1px solid #eee; padding-top: 30px; text-align: center;">Также вы можете заказать индивидуальную медитацию.</div>
            <div class="meditation-bottom-blocks">
                <div class="block">
                    <h4 style="font-family: 'Tenor Sans', sans-serif; font-size: 1.2rem;">Стоимость индивидуальной медитации: 150 BYN</h4>
                    <button class="submit-btn" style="max-width: 280px;" onclick="location.href='catalog.php'">ЗАКАЗАТЬ</button>
                </div>
                <div class="block">
                    <h4 style="font-family: 'Tenor Sans', sans-serif; font-size: 1.2rem;">Получите готовую медитацию в подарок</h4>
                    <button class="submit-btn" style="max-width: 280px;">ПОЛУЧИТЬ В TELEGRAM</button>
                </div>
            </div>
        </section>
    </div>
</div>

<!-- АВТОРИЗАЦИЯ -->
<div class="registration-form" id="registrationForm">
    <div class="registration-form-container">
        <span class="close-btn" onclick="closeAuthModal()">×</span>
        <div class="auth-tabs" style="display: flex; margin-bottom: 20px; border-bottom: 1px solid #ddd;">
            <div class="auth-tab active" id="tabLogin" onclick="toggleAuth('login')" style="flex: 1; text-align: center; padding: 10px; cursor: pointer; font-weight: bold;">Вход</div>
            <div class="auth-tab" id="tabRegister" onclick="toggleAuth('register')" style="flex: 1; text-align: center; padding: 10px; cursor: pointer;">Регистрация</div>
        </div>
        <form id="loginForm" action="auth.php" method="POST">
            <div class="mb-3"><input type="email" name="email" class="form-control joy-input" placeholder="E-mail" required></div>
            <div class="mb-3"><input type="password" name="password" class="form-control joy-input" placeholder="Пароль" required></div>
            <button type="submit" name="login" class="btn-register">ВОЙТИ</button>
        </form>
        <form id="registerForm" action="auth.php" method="POST" style="display: none;">
            <div class="mb-3"><input type="text" name="surname" class="form-control joy-input" placeholder="Фамилия" required></div>
            <div class="mb-3"><input type="text" name="name" class="form-control joy-input" placeholder="Имя" required></div>
            <div class="mb-3"><input type="text" name="patronymic" class="form-control joy-input" placeholder="Отчество"></div>
            <div class="mb-3"><input type="email" name="email" class="form-control joy-input" placeholder="E-mail" required></div>
            <div class="mb-3"><input type="password" name="password" class="form-control joy-input" placeholder="Пароль (мин. 6 символов)" required minlength="6"></div>
            <button type="submit" name="register" class="btn-register">ЗАРЕГИСТРИРОВАТЬСЯ</button>
        </form>
    </div>
</div>

<!-- ОБРАТНАЯ СВЯЗЬ -->
<div class="modal-form" id="callbackModal">
    <div class="form-container">
        <span class="close-btn" onclick="document.getElementById('callbackModal').style.display='none'; document.body.style.overflow='';">&times;</span>
        <div class="form-title">ОБРАТНАЯ СВЯЗЬ</div>
        <p class="text-center text-muted mb-4 small">Оставьте данные, и мы с вами свяжемся.</p>
        <form action="submit_appointment.php" method="POST">
            <input type="hidden" name="topic" value="Обратная связь (Перезвонить)">
            <input type="hidden" name="age" value="Не указан">
            <input type="hidden" name="request" value="Просьба перезвонить клиенту.">
            <input type="hidden" name="specialist_id" value="0">
            <div class="mb-3"><input type="text" name="name" class="form-control joy-input" placeholder="Ваше Имя" required></div>
            <div class="phone-group-container">
                <select class="country-select-joy">
                    <option value="by" selected>BY</option><option value="ru">RU</option>
                </select>
                <input type="tel" name="phone" class="form-control joy-input phone-input-joy" placeholder="+375 (29) 123-45-67" required>
            </div>
            <!-- ГАЛОЧКА ТУТ ТОЖЕ ИСПРАВЛЕНА -->
            <div class="privacy-check-wrapper">
                <input type="checkbox" id="privacyCheckCallback" class="privacy-check-input" required checked>
                <label for="privacyCheckCallback" class="privacy-check-label">Я согласен(на) на обработку персональных данных</label>
            </div>
            <button type="submit" class="submit-btn">ОТПРАВИТЬ ЗАЯВКУ</button>
        </form>
    </div>
</div>

<?php if(!isset($_COOKIE['joy_privacy'])): ?>
<div class="cookie-banner">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-md-9"><p class="small m-0">Мы используем файлы cookie для улучшения работы сайта.</p></div>
            <div class="col-md-3 text-md-right"><form method="POST"><button type="submit" name="accept_cookies" class="main-button small-btn">Хорошо</button></form></div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php
$slotsRaw = $pdo->query("SELECT id, specialist_id, slot_datetime FROM schedule WHERE is_booked = 0 AND slot_datetime > NOW() ORDER BY slot_datetime ASC")->fetchAll();
echo "<script>const availableGlobalSlots = " . json_encode($slotsRaw) . ";</script>";
?>

<div id="toast" class="toast-notification"></div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-bs4.min.js"></script>
<script src="joy.js?v=<?= time() ?>"></script>
</body>
</html>