<?php //profile.php
require_once 'db.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) { header("Location: specialists.php"); exit; }

$stmt = $pdo->prepare("SELECT * FROM specialists WHERE id = ?");
$stmt->execute([$id]);
$spec = $stmt->fetch();

if (!$spec) { header("Location: specialists.php"); exit; }

$stmtReviews = $pdo->prepare("SELECT r.*, u.name as client_name FROM reviews r JOIN users u ON r.user_id = u.id WHERE r.specialist_id = ? AND r.status = 'approved' ORDER BY r.created_at DESC");
$stmtReviews->execute([$id]);
$reviews = $stmtReviews->fetchAll();

$avgRating = 0;
if (count($reviews) > 0) {
    $sum = 0;
    foreach ($reviews as $rev) $sum += $rev['rating'];
    $avgRating = round($sum / count($reviews), 1);
}

$canReview = false;
if (isset($_SESSION['user_id'])) {
    $checkApp = $pdo->prepare("SELECT COUNT(*) FROM appointments WHERE user_id = ? AND specialist_id = ? AND status = 'completed'");
    $checkApp->execute([$_SESSION['user_id'], $id]);
    if ($checkApp->fetchColumn() > 0) $canReview = true;
}

$pageTitle = htmlspecialchars($spec['first_name'] . ' ' . $spec['last_name']) . " | J.O.Y.";
require_once 'header.php';
?>

<section class="section" style="padding-top: 150px; padding-bottom: 100px;">
    
    <div class="mandala-wrapper-quiz">
        <img src="img/Group 186.png" alt="Фон" class="rotating-mandala">
    </div>    

    <div class="container">
        <div class="row mb-4">
            <div class="col-12">
                <a href="specialists.php" class="back-link" style="text-align: left;"><i class="fas fa-arrow-left"></i> К списку психологов</a>
            </div>
        </div>

        <div class="row">
            <!-- ЛЕВАЯ КОЛОНКА (Фото и кратко) -->
            <div class="col-lg-4 mb-5 mb-lg-0 text-center">
                <div class="profile-photo-frame mb-4">
                    <img src="<?= htmlspecialchars($spec['photo']) ?>" alt="<?= htmlspecialchars($spec['first_name']) ?>" onerror="this.src='img/Frame.png'">
                </div>
                <h2 class="profile-name mb-1"><?= htmlspecialchars($spec['first_name'] . ' ' . ($spec['patronymic'] ?? '') . ' ' . $spec['last_name']) ?></h2>
                <p class="profile-role mb-3"><?= htmlspecialchars($spec['specialization']) ?></p>
                
                <div class="profile-stats-container">
                    <div class="profile-stat-box">
                        <div class="profile-stat-number"><?= $spec['experience_years'] ?></div>
                        <div class="profile-stat-label">Лет опыта</div>
                    </div>
                    <div class="profile-stat-box">
                        <div class="profile-stat-number"><i class="fas fa-star rating-stars-gold"></i> <?= $avgRating > 0 ? $avgRating : '—' ?></div>
                        <div class="profile-stat-label">Рейтинг</div>
                    </div>
                </div>

                <?php
                $stmtSlots = $pdo->prepare("SELECT * FROM schedule WHERE specialist_id = ? AND is_booked = 0 AND slot_datetime > NOW() ORDER BY slot_datetime ASC LIMIT 6");
                $stmtSlots->execute([$id]);
                $freeSlots = $stmtSlots->fetchAll();
                ?>

                <div class="card-action-request-bg text-left mb-4">
                    <h6 class="font-weight-bold mb-3">Свободное время для записи:</h6>
                    <?php if (count($freeSlots) > 0): ?>
                        <div class="d-flex flex-wrap">
                            <?php foreach ($freeSlots as $slot): 
                                $niceDate = date('d.m', strtotime($slot['slot_datetime']));
                                $niceTime = date('H:i', strtotime($slot['slot_datetime']));
                            ?>
                                <button class="btn btn-outline-dark btn-sm slot-btn-profile" 
                                        onclick="openAppointmentForSpec(<?= $spec['id'] ?>, '<?= htmlspecialchars($spec['first_name'].' '.$spec['last_name']) ?>', <?= $slot['id'] ?>, '<?= $niceDate ?> в <?= $niceTime ?>', event)">
                                    <?= $niceDate ?> | <?= $niceTime ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                        <small class="text-muted d-block mt-2">Нажмите на удобное время, чтобы забронировать его.</small>
                    <?php else: ?>
                        <p class="text-muted m-0 small">В данный момент нет открытых окон. Оставьте запрос куратору.</p>
                        <button class="main-button small-btn w-100 mt-3" onclick="document.getElementById('callbackModal').style.display='flex'; event.preventDefault();">Обратная связь</button>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ПРАВАЯ КОЛОНКА (Инфо) -->
            <div class="col-lg-8">
                <ul class="nav nav-tabs profile-tabs-header mb-4" id="profileTab" role="tablist">
                    <li class="nav-item"><a class="nav-link active" id="about-tab" data-toggle="tab" href="#about" role="tab">Обо мне</a></li>
                    <li class="nav-item"><a class="nav-link" id="reviews-tab" data-toggle="tab" href="#reviews" role="tab">Отзывы (<?= count($reviews) ?>)</a></li>
                    <li class="nav-item"><a class="nav-link" id="articles-tab" data-toggle="tab" href="#articles" role="tab">Статьи</a></li>
                </ul>

                <div class="tab-content" id="profileTabContent">
                    
                    <div class="tab-pane fade show active" id="about" role="tabpanel">
                        
                        <?php if(!empty($spec['directions'])): ?>
                            <div class="mb-4">
                                <h4 class="font-tenor mb-3">Направления работы</h4>
                                <div class="directions-list">
                                    <?php foreach(explode(',', $spec['directions']) as $d): ?>
                                        <?php if(trim($d) !== ''): ?>
                                        <span class="direction-tag"><?= htmlspecialchars(trim($d)) ?></span>
                                        <?php endif; ?>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <h4 class="font-tenor mb-3">Образование</h4>
                        <div class="profile-info-card">
                            <p class="m-0 profile-text-content"><?= nl2br(htmlspecialchars($spec['education'])) ?></p>
                        </div>

                        <h4 class="font-tenor mb-3">О подходе</h4>
                        <div class="mb-4 profile-text-content">
                            <?= nl2br(htmlspecialchars($spec['description'])) ?>
                        </div>

                        <?php if(!empty($spec['block1_title'])): ?>
                            <div class="mb-4">
                                <h4 class="font-tenor mb-3"><?= htmlspecialchars($spec['block1_title']) ?></h4>
                                <div class="profile-text-content"><?= nl2br(htmlspecialchars($spec['block1_text'])) ?></div>
                            </div>
                        <?php endif; ?>

                        <?php if(!empty($spec['block2_title'])): ?>
                            <div class="mb-4">
                                <h4 class="font-tenor mb-3"><?= htmlspecialchars($spec['block2_title']) ?></h4>
                                <div class="profile-text-content"><?= nl2br(htmlspecialchars($spec['block2_text'])) ?></div>
                            </div>
                        <?php endif; ?>

                    </div>

                    <div class="tab-pane fade" id="reviews" role="tabpanel">
                        <div class="d-flex justify-content-between align-items-center mb-4 border-bottom pb-3">
                            <h4 class="m-0 font-tenor">Отзывы клиентов</h4>
                            <?php if(isset($_SESSION['user_id'])): ?>
                                <?php if($canReview): ?>
                                    <button class="main-button small-btn" onclick="document.getElementById('reviewModal').style.display='flex'">Оставить отзыв</button>
                                <?php else: ?>
                                    <span class="badge badge-light border text-muted">Отзыв после сессии</span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span class="text-muted small">Для отзыва <a href="#" class="color-accent-joy" onclick="openAuthModal(event)">войдите в аккаунт</a>.</span>
                            <?php endif; ?>
                        </div>
                        <?php if (count($reviews) == 0): ?>
                            <p class="text-muted text-center py-5">Одобренных отзывов пока нет.</p>
                        <?php else: ?>
                            <div class="reviews-list">
                                <?php foreach ($reviews as $rev): ?>
                                    <div class="review-item-card">
                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                            <h5 class="m-0 font-weight-bold"><?= htmlspecialchars($rev['client_name']) ?></h5>
                                            <div class="rating-stars-gold">
                                                <?php for($i=1; $i<=5; $i++): ?>
                                                    <i class="fas fa-star <?= $i <= $rev['rating'] ? '' : 'text-light' ?>"></i>
                                                <?php endfor; ?>
                                            </div>
                                        </div>
                                        <small class="text-muted d-block mb-3"><?= date('d.m.Y', strtotime($rev['created_at'])) ?></small>
                                        <p class="m-0 profile-text-content"><?= nl2br(htmlspecialchars($rev['review_text'])) ?></p>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="tab-pane fade" id="articles" role="tabpanel">
                        <h4 class="font-tenor mb-4">Публикации психолога</h4>
                        <?php
                        $stmtPosts = $pdo->prepare("SELECT * FROM posts WHERE author_id = ? AND (status = 'published' OR status IS NULL) ORDER BY created_at DESC");
                        $stmtPosts->execute([$id]);
                        $myPosts = $stmtPosts->fetchAll();
                        ?>
                        <?php if (count($myPosts) == 0): ?>
                            <p class="text-muted text-center py-5">Публикаций пока нет.</p>
                        <?php else: ?>
                            <div class="row">
                                <?php foreach($myPosts as $post): ?>
                                <div class="col-md-6 mb-4">
                                    <div class="card h-100 border-0 shadow-sm" style="border-radius: 15px;">
                                        <img src="<?= htmlspecialchars($post['image']) ?>" class="card-img-top" alt="Обложка" style="border-radius: 15px 15px 0 0; height: 180px; object-fit: cover;">
                                        <div class="card-body d-flex flex-column p-3">
                                            <h6 class="font-weight-bold font-tenor"><?= htmlspecialchars($post['title']) ?></h6>
                                            <a href="article.php?id=<?= $post['id'] ?>" class="color-accent-joy mt-auto stretched-link">Читать далее &rarr;</a>
                                        </div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
</section>

<!-- МОДАЛКА ОТЗЫВА -->
<div class="modal-form" id="reviewModal">
    <form class="form-container" action="submit_review.php" method="POST">
        <span class="close-btn" onclick="document.getElementById('reviewModal').style.display='none'">&times;</span>
        <div class="form-title">ВАШ ОТЗЫВ</div>
        <input type="hidden" name="specialist_id" value="<?= $spec['id'] ?>">
        <div class="mb-3 text-center">
            <label class="font-weight-bold">Оценка:</label>
            <select name="rating" class="form-control joy-select text-center font-weight-bold">
                <option value="5">⭐⭐⭐⭐⭐ (Отлично)</option>
                <option value="4">⭐⭐⭐⭐ (Хорошо)</option>
                <option value="3">⭐⭐⭐ (Нормально)</option>
                <option value="2">⭐⭐ (Плохо)</option>
                <option value="1">⭐ (Ужасно)</option>
            </select>
        </div>
        <div class="mb-4">
            <label class="font-weight-bold">Текст:</label>
            <textarea class="form-control joy-input" name="review_text" rows="4" placeholder="Расскажите о впечатлениях..." required></textarea>
        </div>
        <button type="submit" class="submit-btn">ОТПРАВИТЬ</button>
    </form>
</div>

<?php require_once 'footer.php'; ?>