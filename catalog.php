<?php //catalog.php
require_once 'header.php'; 

$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$sort = $_GET['sort'] ?? 'new';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 6; 
$offset = ($page - 1) * $limit;

$sqlBase = "FROM products WHERE 1=1";
$params = [];

if ($category) {
    $sqlBase .= " AND category = :category";
    $params[':category'] = $category;
}

if ($search) {
    $sqlBase .= " AND title LIKE :search";
    $params[':search'] = "%$search%";
}

$orderSql = "ORDER BY id DESC"; 
if ($sort == 'price_asc') {
    $orderSql = "ORDER BY price ASC";
} elseif ($sort == 'price_desc') {
    $orderSql = "ORDER BY price DESC";
} elseif ($sort == 'old') {
    $orderSql = "ORDER BY id ASC";
}

$countStmt = $pdo->prepare("SELECT COUNT(*) $sqlBase");
$countStmt->execute($params);
$totalProducts = $countStmt->fetchColumn();
$totalPages = ceil($totalProducts / $limit);

$sql = "SELECT * $sqlBase $orderSql LIMIT $limit OFFSET $offset";
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();
?>

<section class="section catalog-main-section">
    <div class="mandala-wrapper-header">
        <img src="img/Group 186.png" alt="Фон" class="rotating-mandala">
    </div>
    <div class="container">
        <div class="text-center mb-5">
            <h2 class="section-title">ОНЛАЙН-ПРОДУКТЫ</h2>
            <h3 class="subsection-title">Выберите медитации и курсы</h3>
        </div>
        
        <!-- ПАНЕЛЬ ФИЛЬТРОВ -->
        <div class="filter-panel mb-5">
            <form method="GET" action="catalog.php" id="catalogFilterForm" class="row justify-content-center align-items-center">
                
                <div class="col-md-4 mb-3">
                    <input type="text" name="search" id="searchInput" class="form-control joy-input" placeholder="Поиск по названию..." value="<?= htmlspecialchars($search) ?>">
                </div>

                <div class="col-md-4 mb-3">
                    <select name="category" class="form-control joy-select" onchange="this.form.submit()">
                        <option value="">Все категории</option>
                        <option value="meditation" <?= $category == 'meditation' ? 'selected' : '' ?>>Медитации</option>
                        <option value="course" <?= $category == 'course' ? 'selected' : '' ?>>Курсы</option>
                        <option value="club" <?= $category == 'club' ? 'selected' : '' ?>>Закрытый клуб</option>
                    </select>
                </div>

                <div class="col-md-4 mb-3">
                    <select name="sort" class="form-control joy-select" onchange="this.form.submit()">
                        <option value="new" <?= $sort == 'new' ? 'selected' : '' ?>>Сначала новые</option>
                        <option value="old" <?= $sort == 'old' ? 'selected' : '' ?>>Сначала старые</option>
                        <option value="price_asc" <?= $sort == 'price_asc' ? 'selected' : '' ?>>Сначала дешевые</option>
                        <option value="price_desc" <?= $sort == 'price_desc' ? 'selected' : '' ?>>Сначала дорогие</option>
                    </select>
                </div>
                
                <?php if($search || $category || $sort != 'new'): ?>
                <div class="col-12 text-center mt-2">
                    <a href="catalog.php" class="text-muted small text-underline">Сбросить фильтры</a>
                </div>
                <?php endif; ?>
            </form>
        </div>
        
        <!-- СЕТКА ТОВАРОВ -->
        <div class="row" id="catalog-grid">
            <?php if(count($products) > 0): ?>
                <?php foreach ($products as $row): ?>
                <div class="col-md-4 col-sm-6 mb-4">
                    <div class="catalog-item">
                        <div class="catalog-img-circle">
                            <img src="<?= htmlspecialchars($row['image']) ?>" alt="<?= htmlspecialchars($row['title']) ?>" onerror="this.src='img/Frame.png'">
                        </div>
                        <h5 class="font-tenor"><?= htmlspecialchars($row['title']) ?></h5>
                        <p class="catalog-item-desc"><?= htmlspecialchars($row['description']) ?></p>
                        <div class="catalog-item-price"><?= $row['price'] ?> BYN</div>
                        <button class="main-button small-btn" 
                                onclick="addToCart(<?= $row['id'] ?>, '<?= htmlspecialchars($row['title']) ?>', <?= $row['price'] ?>, '<?= htmlspecialchars($row['image']) ?>')">
                            В корзину
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="col-12 text-center py-5">
                    <h4 class="text-muted">Ничего не найдено :(</h4>
                    <p class="small">Попробуйте изменить параметры поиска или фильтрации.</p>
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
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">&laquo;</a>
                        </li>
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                        <li class="page-item <?= ($page == $i) ? 'active' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                        <li class="page-item <?= ($page >= $totalPages) ? 'disabled' : '' ?>">
                            <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">&raquo;</a>
                        </li>
                    </ul>
                </nav>
            </div>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Скрипт автоотправки поиска -->
<script>
    let timeout;
    const sInput = document.getElementById('searchInput');
    if(sInput) {
        sInput.addEventListener('input', function() {
            clearTimeout(timeout);
            timeout = setTimeout(() => { document.getElementById('catalogFilterForm').submit(); }, 600);
        });
    }
</script>

<?php require_once 'footer.php'; ?>