<?php //services.php
require_once 'db.php';

// Безопасное создание таблиц и миграция колонки room_id
try {
    $pdo->exec("CREATE TABLE IF NOT EXISTS therapy_groups (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(255), description TEXT, event_date DATETIME, max_seats INT, spec_id INT, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
} catch(Exception $e) {}

try {
    $pdo->exec("ALTER TABLE therapy_groups ADD COLUMN room_id VARCHAR(50) DEFAULT ''");
} catch(Exception $e) {}

$stmt = $pdo->query("SELECT * FROM services ORDER BY price ASC");
$services = $stmt->fetchAll();

// ИСПРАВЛЕННЫЙ ЗАПРОС: Показываем группы от сегодняшнего дня и далее
$groups = $pdo->query("SELECT g.*, s.first_name, s.last_name FROM therapy_groups g LEFT JOIN specialists s ON g.spec_id = s.id WHERE DATE(g.event_date) >= CURRENT_DATE ORDER BY g.event_date ASC")->fetchAll();

$pageTitle = "Консультации | J.O.Y.";
require_once 'header.php';
?>

<section class="section" style="padding-top: 150px; padding-bottom: 100px;">
    <div class="mandala-wrapper-header">
        <img src="img/Group 186.png" alt="Фон" class="rotating-mandala">
    </div>
    <div class="container">
        
        <!-- Заголовок страницы -->
        <div class="row justify-content-center mb-5">
            <div class="col-lg-8 text-center">
                <h2 class="section-title mb-3">НАШИ УСЛУГИ</h2>
                <p class="section-text mx-auto" style="max-width: 600px;">Мы предлагаем различные форматы работы, чтобы терапия была максимально комфортной и эффективной именно для вас.</p>
            </div>
        </div>

        <!-- Карточки цен -->
        <div class="row justify-content-center">
            <?php foreach($services as $service): ?>
                <div class="col-lg-4 col-md-6 mb-4">
                    <div class="service-price-card h-100 d-flex flex-column shadow-sm">
                        <div class="mb-3">
                            <?php if($service['type'] == 'individual'): ?>
                                <i class="fas fa-user service-icon-accent"></i>
                            <?php elseif($service['type'] == 'family'): ?>
                                <i class="fas fa-user-friends service-icon-accent"></i>
                            <?php elseif($service['type'] == 'online'): ?>
                                <i class="fas fa-laptop service-icon-accent"></i>
                            <?php else: ?>
                                <i class="fas fa-hands-helping service-icon-accent"></i>
                            <?php endif; ?>
                        </div>

                        <h4 class="mb-3 font-tenor"><?= htmlspecialchars($service['title']) ?></h4>
                        
                        <div class="price-box mb-4 mt-auto">
                            <div class="price-value-big"><?= $service['price'] ?> <span class="small">BYN</span></div>
                            <div class="text-muted small">Длительность: <?= $service['duration_min'] ?> минут</div>
                        </div>

                        <button class="main-button small-btn w-100" onclick="openAppointmentForService('<?= htmlspecialchars($service['title']) ?>', event)">Записаться</button>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <!-- БЛОК: ГРУППОВАЯ ТЕРАПИЯ -->
        <?php if(count($groups) > 0): ?>
        <div class="row justify-content-center mt-5 pt-5 border-top">
            <div class="col-lg-10 text-center mb-5">
                <h2 class="section-title">ГРУППОВАЯ ТЕРАПИЯ</h2>
            </div>
            
            <div class="col-lg-10">
                <?php foreach($groups as $group): 
                    $countStmt = $pdo->prepare("SELECT COUNT(*) FROM group_participants WHERE group_id = ? AND status='active'");
                    $countStmt->execute([$group['id']]);
                    $occupied = $countStmt->fetchColumn();
                    $isFull = $occupied >= $group['max_seats'];
                    
                    $maxS = (int)$group['max_seats'] > 0 ? (int)$group['max_seats'] : 1;
                    $percent = ($occupied / $maxS) * 100;
                    if ($percent > 100) $percent = 100;

                    $specName = trim(($group['first_name'] ?? '') . ' ' . ($group['last_name'] ?? ''));
                    if (empty($specName)) $specName = "Специалист центра";
                ?>
                <div class="group-card-joy mb-4">
                    <div class="row align-items-center">
                        <div class="col-md-8">
                            <h4 class="font-weight-bold text-dark-joy"><?= htmlspecialchars($group['title']) ?></h4>
                            <div class="text-muted mb-2 small">
                                <i class="far fa-calendar-alt mr-2"></i> <?= date('d.m.Y в H:i', strtotime($group['event_date'])) ?> 
                                <span class="mx-2">|</span> 
                                <i class="fas fa-user-md mr-1"></i> <?= htmlspecialchars($specName) ?>
                                <?php if(!empty($group['room_id'])): ?>
                                    <span class="mx-2">|</span> 
                                    <i class="fas fa-door-open mr-1"></i> Каб: <?= htmlspecialchars($group['room_id']) ?>
                                <?php endif; ?>
                            </div>
                            <p class="small mb-3"><?= nl2br(htmlspecialchars($group['description'])) ?></p>
                            
                            <!-- Прогресс мест -->
                            <div class="progress joy-progress-container mb-2">
                                <div class="progress-bar <?= $isFull ? 'bg-danger' : 'joy-progress-bar' ?>" role="progressbar" style="width: <?= $percent ?>%;"></div>
                            </div>
                            <small class="group-status-info <?= $isFull ? 'text-danger' : 'text-success' ?>">
                                <?= $isFull ? '<i class="fas fa-lock mr-1"></i> Мест нет. Доступен лист ожидания.' : "<i class='fas fa-lock-open mr-1'></i> Осталось мест: " . ($group['max_seats'] - $occupied) . " из " . $group['max_seats'] ?>
                            </small>
                        </div>
                        <div class="col-md-4 text-md-right mt-3 mt-md-0">
                            <form method="POST" action="join_group.php">
                                <input type="hidden" name="group_id" value="<?= $group['id'] ?>">
                                <?php if($isFull): ?>
                                    <button type="submit" name="join" class="btn btn-outline-danger waitlist-btn-outline">В лист ожидания</button>
                                <?php else: ?>
                                    <button type="submit" name="join" class="main-button small-btn">Записаться</button>
                                <?php endif; ?>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- БЛОК: КАК ВЫБРАТЬ ФОРМАТ -->
        <div class="row justify-content-center mt-5 pt-4 border-top">
            <div class="col-lg-10">
                <h3 class="mb-5 text-center font-tenor">Как выбрать подходящий формат?</h3>
                <div class="row">
                    <div class="col-md-4 mb-4">
                        <h5 class="font-weight-bold color-accent-joy"><i class="fas fa-check-circle format-icon-check"></i> Очно в Минске</h5>
                        <p class="text-muted small format-text-small">Классический вариант терапии. Идеален для тех, кому важна смена обстановки и максимальное погружение в процесс без отвлекающих факторов.</p>
                    </div>
                    <div class="col-md-4 mb-4">
                        <h5 class="font-weight-bold color-accent-joy"><i class="fas fa-check-circle format-icon-check"></i> Онлайн-сессия</h5>
                        <p class="text-muted small format-text-small">Подходит, если вы живете в другом городе или цените экономию времени на дорогу. Эффективность работы в онлайне ничем не уступает встречам в офисе.</p>
                    </div>
                    <div class="col-md-4 mb-4">
                        <h5 class="font-weight-bold color-accent-joy"><i class="fas fa-check-circle format-icon-check"></i> Парная работа</h5>
                        <p class="text-muted small format-text-small">Помогает партнерам услышать друг друга в безопасной среде. На сессии всегда присутствуют оба партнера. Длительность увеличена до 90 минут.</p>
                    </div>
                </div>
            </div>
        </div>

    </div>
</section>

<?php require_once 'footer.php'; ?>