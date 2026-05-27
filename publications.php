<?php //publications.php
require_once 'header.php'; 

// ПАРАМЕТРЫ ПАГИНАЦИИ
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 6; // Количество статей на одной странице
$offset = ($page - 1) * $limit;

// Фикс базы данных: на всякий случай
try { $pdo->exec("ALTER TABLE posts ADD COLUMN status VARCHAR(20) DEFAULT 'published'"); } catch (Exception $e) {}

// Подсчет общего количества опубликованных статей
$countStmt = $pdo->query("SELECT COUNT(*) FROM posts WHERE status = 'published' OR status IS NULL");
$totalPosts = $countStmt->fetchColumn();
$totalPages = ceil($totalPosts / $limit);

// Получение статей для текущей страницы
$stmt = $pdo->prepare("SELECT * FROM posts WHERE status = 'published' OR status IS NULL ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
$stmt->execute();
$articles = $stmt->fetchAll();
?>

<section class="section" style="padding-top: 150px; padding-bottom: 100px; background: white;">
    <div class="mandala-wrapper-header">
        <img src="img/Group 186.png" alt="Фон" class="rotating-mandala">
    </div>
    <div class="container">
        <div class="row justify-content-center mb-5">
            <div class="col-lg-10 text-center">
                <h2 class="section-title">ПРОСТРАНСТВО ЗНАНИЙ</h2>
                <h3 class="subsection-title">Наши публикации</h3>
            </div>
        </div>
        
        <div class="row publications-grid" id="publicationsList">
            <?php if(count($articles) > 0): ?>
                <?php foreach ($articles as $article): ?>
                <div class="col-md-4 mb-4">
                    <div class="card h-100" style="border: none; box-shadow: 0 5px 15px rgba(0,0,0,0.05); border-radius: 15px;">
                        <!-- Обложка карточки -->
                        <img src="<?= htmlspecialchars($article['image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($article['title']) ?>" 
                             style="border-radius: 15px 15px 0 0; height: 250px; object-fit: cover;" onerror="this.src='img/Frame.png'">
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title font-weight-bold" style="font-family: 'Tenor Sans', sans-serif;"><?= htmlspecialchars($article['title']) ?></h5>
                            <p class="card-text text-muted" style="font-family: 'Lato', sans-serif; flex-grow: 1;"><?= htmlspecialchars($article['short_desc']) ?></p>
                            <a href="article.php?id=<?= $article['id'] ?>" class="btn-link stretched-link" style="color: #E0C6AD; font-weight: bold; text-decoration: none;">Читать далее &rarr;</a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <h4 class="text-muted">Публикаций пока нет.</h4>
                </div>
            <?php endif; ?>
        </div>

        <!-- ПАГИНАЦИЯ -->
        <?php if ($totalPages > 1): ?>
        <div class="row mt-5">
            <div class="col-12">
                <nav>
                    <ul class="pagination justify-content-center joy-pagination">
                        <li class="page-item <?= ($page <= 1) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page - 1 ?>">&laquo;</a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>

                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?page=<?= $page + 1 ?>">&raquo;</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        <?php endif; ?>

    </div>
</section>

<?php require_once 'footer.php'; ?>