<?php //specialists.php
require_once 'db.php';

// Вытягиваем всех психологов из базы
$stmt = $pdo->query("SELECT * FROM specialists ORDER BY id ASC");
$specialists = $stmt->fetchAll();

$pageTitle = "Наши специалисты | J.O.Y.";
require_once 'header.php';
?>

<section class="section" style="padding-top: 150px; padding-bottom: 100px;">
    <div class="mandala-wrapper-quiz">
        <img src="img/Group 186.png" alt="Фон" class="rotating-mandala">
    </div>
    <div class="container">
        
        <!-- ЖУРНАЛЬНАЯ ВЕРСТКА ЗАГОЛОВКА -->
        <div class="row align-items-center mb-5 pb-4">
            <!-- Левая часть: Заголовки -->
            <div class="col-lg-5 mb-4 mb-lg-0 pr-lg-5">
                <h3 class="section-title text-left m-0" style="font-size: 2.4rem;">КОМАНДА ЦЕНТРА</h3>
                <h2 class="subsection-title text-left mt-2 mb-0" style="font-size: 1.4rem; color: #E0C6AD; line-height: 1.2;">Выберите своего психолога</h2>
            </div>
            
            <!-- Правая часть: Воодушевляющий текст с линией -->
            <div class="col-lg-7">
                <p class="specialists-header-desc">
                    Каждый специалист нашего центра — это не просто дипломированный психолог, а бережный проводник, прошедший строгий профессиональный отбор. Мы объединили экспертов с разными терапевтическими подходами, чтобы вы смогли найти именно того человека, с которым почувствуете абсолютную безопасность, тепло и поддержку.<br><br>
                    <strong>Прислушайтесь к себе:</strong> выберите того, чей опыт откликается вам больше всего, и сделайте первый, самый важный шаг навстречу внутренней гармонии.
                </p>
            </div>
        </div>

        <!-- КНОПКИ ФИЛЬТРАЦИИ (КАПСУЛА) -->
        <div class="row justify-content-center mb-5">
            <div class="col-12 text-center">
                <div class="filters-group">
                    <button class="filter-btn active" data-filter="all">Все специалисты</button>
                    <button class="filter-btn" data-filter="семейн">Семейные</button>
                    <button class="filter-btn" data-filter="тревог">Тревожность и депрессия</button>
                    <button class="filter-btn" data-filter="бизнес">Бизнес-коучинг</button>
                </div>
            </div>
        </div>

        <!-- СЕТКА СПЕЦИАЛИСТОВ -->
        <div class="row justify-content-center" id="specialists-grid">
            <?php foreach($specialists as $spec): ?>
                <div class="col-lg-4 col-md-6 mb-5 spec-card-wrapper" data-specs="<?= mb_strtolower($spec['specialization'] . ' ' . $spec['description']) ?>">
                    <div class="specialist-card w-100 h-100 d-flex flex-column">
                        <img src="<?= htmlspecialchars($spec['photo']) ?>" alt="<?= htmlspecialchars($spec['first_name']) ?>" class="specialist-photo" onerror="this.src='img/Frame.png'">
                        <div class="specialist-name"><?= htmlspecialchars($spec['first_name'] . ' ' . $spec['last_name']) ?></div>
                        <div class="specialist-role mb-2"><?= htmlspecialchars($spec['specialization']) ?></div>
                        <div class="text-muted small mb-3">Опыт работы: <?= $spec['experience_years'] ?> лет</div>
                        <a href="profile.php?id=<?= $spec['id'] ?>" class="main-button small-btn mt-auto">Подробнее</a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- БЛОК КВИЗА (ТА САМАЯ ПАНЕЛЬКА) -->
        <div class="row justify-content-center mt-5">
            <div class="col-lg-8">
                <div class="quiz-banner">
                    <h3 class="mb-3" style="font-family: 'Tenor Sans', sans-serif;">Не знаете, кого выбрать?</h3>
                    <p class="mb-4">Пройдите короткий тест из 5-х вопросов, и наш алгоритм подберет специалиста, который идеально подойдет именно под ваш запрос.</p>
                    <button class="consultation-button mx-auto" onclick="document.getElementById('quizModal').style.display='flex'">ПОДОБРАТЬ ПСИХОЛОГА</button>
                </div>
            </div>
        </div>

    </div>
</section>

<!-- МОДАЛЬНОЕ ОКНО КВИЗА -->
<div class="modal-form" id="quizModal">
    <div class="form-container quiz-modal-container">
        <span class="close-btn" onclick="document.getElementById('quizModal').style.display='none'; document.body.style.overflow='';">&times;</span>
        
        <div class="quiz-step" id="q-step-1">
            <span class="quiz-step-badge mb-2">Шаг 1 из 5</span>
            <h4 class="font-weight-bold mb-3 font-tenor">Для кого Вы подбираете психолога?</h4>
            <div class="d-flex flex-column">
                <button class="quiz-btn" onclick="nextQuizStep(2, 'individual')"><i class="fas fa-user"></i> Для себя (Индивидуальная терапия)</button>
                <button class="quiz-btn" onclick="nextQuizStep(2, 'couples')"><i class="fas fa-user-friends"></i> Для пары (Семейные сложности)</button>
                <button class="quiz-btn" onclick="nextQuizStep(2, 'child')"><i class="fas fa-child"></i> Для ребёнка / подростка</button>
                <button class="quiz-btn" onclick="nextQuizStep(2, 'family')"><i class="fas fa-users"></i> Для семьи (Родители и дети)</button>
            </div>
        </div>

        <div class="quiz-step" id="q-step-2" style="display: none;">
            <span class="quiz-step-badge mb-2">Шаг 2 из 5</span>
            <h4 class="font-weight-bold mb-3 font-tenor">Какая тема наиболее актуальна сейчас?</h4>
            <div class="d-flex flex-column">
                <button class="quiz-btn" onclick="nextQuizStep(3, 'emotional')"><i class="fas fa-heart-broken"></i> Эмоции (Страхи, тревога)</button>
                <button class="quiz-btn" onclick="nextQuizStep(3, 'relationship')"><i class="fas fa-comments"></i> Отношения (Конфликты)</button>
                <button class="quiz-btn" onclick="nextQuizStep(3, 'self')"><i class="fas fa-compass"></i> Самоопределение (Карьера)</button>
                <button class="quiz-btn" onclick="nextQuizStep(3, 'family_child')"><i class="fas fa-baby"></i> Воспитание (Кризисы развития)</button>
            </div>
        </div>

        <div class="quiz-step" id="q-step-3" style="display: none;">
            <span class="quiz-step-badge mb-2">Шаг 3 из 5</span>
            <h4 class="font-weight-bold mb-3 font-tenor">Какой формат работы Вы предпочитаете?</h4>
            <div class="d-flex flex-column">
                <button class="quiz-btn" onclick="nextQuizStep(4, 'offline')"><i class="fas fa-map-marker-alt"></i> Лично в офисе (Минск)</button>
                <button class="quiz-btn" onclick="nextQuizStep(4, 'online')"><i class="fas fa-video"></i> Онлайн сессия (Zoom, Telegram)</button>
                <button class="quiz-btn" onclick="nextQuizStep(4, 'any')"><i class="fas fa-sync"></i> Не имеет значения</button>
            </div>
        </div>

        <div class="quiz-step" id="q-step-4" style="display: none;">
            <span class="quiz-step-badge mb-2">Шаг 4 из 5</span>
            <h4 class="font-weight-bold mb-3 font-tenor">Какого терапевтического стиля вы ожидаете?</h4>
            <div class="d-flex flex-column">
                <button class="quiz-btn" onclick="nextQuizStep(5, 'soft')"><i class="fas fa-feather-alt"></i> Мягкий и бережный (Эмпатия)</button>
                <button class="quiz-btn" onclick="nextQuizStep(5, 'structured')"><i class="fas fa-tasks"></i> Структурированный подход (КПТ)</button>
                <button class="quiz-btn" onclick="nextQuizStep(5, 'active')"><i class="fas fa-rocket"></i> Четкий коучинг (На результат)</button>
            </div>
        </div>

        <div class="quiz-step" id="q-step-5" style="display: none;">
            <span class="quiz-step-badge mb-2">Шаг 5 из 5</span>
            <h4 class="font-weight-bold mb-3 font-tenor">Пол специалиста</h4>
            <div class="d-flex flex-column">
                <button class="quiz-btn" onclick="finishQuiz('female')"><i class="fas fa-venus"></i> Психолог-женщина</button>
                <button class="quiz-btn" onclick="finishQuiz('male')"><i class="fas fa-mars"></i> Психолог-мужчина</button>
                <button class="quiz-btn" onclick="finishQuiz('any')"><i class="fas fa-genderless"></i> Не имеет значения</button>
            </div>
        </div>

        <div class="quiz-step" id="q-step-result" style="display: none;">
            <span class="badge mb-2" style="background: #eaf4ea; color: #4a934a; border: 1px solid #4a934a; padding: 5px 12px; border-radius: 10px;">Успешный подбор</span>
            <h3 class="font-weight-bold mb-2 font-tenor">Рекомендуемый специалист</h3>
            <div id="quiz-result-card" class="quiz-result-card">
                <!-- Сюда JS вставит карточку -->
            </div>
            <button class="main-button small-btn w-100" id="quiz-book-btn" onclick="bookFromQuiz()">ЗАПИСАТЬСЯ НА СЕССИЮ</button>
            <a href="#" class="back-link" onclick="resetQuiz()">Пройти тест заново</a>
        </div>
    </div>
</div>
<?php require_once 'footer.php'; ?>