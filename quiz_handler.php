<?php //quiz_handler.php
require_once 'db.php';
header('Content-Type: application/json');

$target   = $_POST['target'] ?? '';
$problem  = $_POST['problem'] ?? '';
$format   = $_POST['format'] ?? '';
$style    = $_POST['style'] ?? '';
$gender   = $_POST['gender'] ?? '';

// 1. Получаем список всех доступных специалистов
$stmt = $pdo->query("SELECT id, first_name, patronymic, last_name, specialization, description, directions, photo FROM specialists");
$specialists = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($specialists)) {
    echo json_encode(['success' => false]);
    exit;
}

$scoredSpecs = [];

foreach ($specialists as $spec) {
    $score = 0;
    
    // Объединяем текстовые поля для поиска совпадений ключевых слов (регистронезависимый поиск)
    $searchArea = mb_strtolower($spec['specialization'] . ' ' . $spec['description'] . ' ' . ($spec['directions'] ?? ''));

    // --- ФАКТОР 1: Кому подбираем (ЦА) ---
    if ($target === 'couples') {
        if (mb_strpos($searchArea, 'семейн') !== false || mb_strpos($searchArea, 'парн') !== false || mb_strpos($searchArea, 'отношен') !== false || mb_strpos($searchArea, 'брак') !== false) {
            $score += 15;
        }
    } elseif ($target === 'child') {
        if (mb_strpos($searchArea, 'детск') !== false || mb_strpos($searchArea, 'подростк') !== false || mb_strpos($searchArea, 'ребен') !== false || mb_strpos($searchArea, 'возраст') !== false) {
            $score += 15;
        }
    } elseif ($target === 'family') {
        if (mb_strpos($searchArea, 'семейн') !== false || mb_strpos($searchArea, 'родител') !== false || mb_strpos($searchArea, 'детск') !== false) {
            $score += 12;
        }
    }

    // --- ФАКТОР 2: Психологический запрос ---
    if ($problem === 'emotional') {
        $keywords = ['тревог', 'страх', 'выгорани', 'апати', 'депресс', 'кпт', 'панич', 'навязчив'];
        foreach ($keywords as $kw) {
            if (mb_strpos($searchArea, $kw) !== false) $score += 4;
        }
    } elseif ($problem === 'relationship') {
        $keywords = ['отношени', 'конфликт', 'измен', 'развод', 'одиночеств', 'партнер'];
        foreach ($keywords as $kw) {
            if (mb_strpos($searchArea, $kw) !== false) $score += 4;
        }
    } elseif ($problem === 'self') {
        $keywords = ['самооценк', 'бизнес', 'карьер', 'коуч', 'границ', 'уверенност', 'развити'];
        foreach ($keywords as $kw) {
            if (mb_strpos($searchArea, $kw) !== false) $score += 4;
        }
    } elseif ($problem === 'family_child') {
        $keywords = ['детск', 'подростк', 'воспитани', 'кризис', 'родител', 'поведени'];
        foreach ($keywords as $kw) {
            if (mb_strpos($searchArea, $kw) !== false) $score += 5;
        }
    }

    // --- ФАКТОР 3: Стиль взаимодействия ---
    if ($style === 'soft') {
        $keywords = ['бережно', 'эмпати', 'поддержк', 'гештальт', 'приняти', 'гуманитар'];
        foreach ($keywords as $kw) {
            if (mb_strpos($searchArea, $kw) !== false) $score += 3;
        }
    } elseif ($style === 'structured') {
        $keywords = ['кпт', 'структур', 'анализ', 'задани', 'логика', 'рациональ'];
        foreach ($keywords as $kw) {
            if (mb_strpos($searchArea, $kw) !== false) $score += 3;
        }
    } elseif ($style === 'active') {
        $keywords = ['коуч', 'активн', 'цели', 'бизнес', 'результат', 'мотиваци'];
        foreach ($keywords as $kw) {
            if (mb_strpos($searchArea, $kw) !== false) $score += 3;
        }
    }

    // --- ФАКТОР 4: Пол психолога ---
    // Определение пола по окончаниям имен (а, я, и — женские, остальные — мужские)
    $firstName = mb_strtolower($spec['first_name']);
    $lastChar = mb_substr($firstName, -1);
    $isFemaleName = in_array($lastChar, ['а', 'я', 'и']);

    if ($gender === 'female') {
        if ($isFemaleName) $score += 10;
        else $score -= 8; // Штраф для несоответствующего пола
    } elseif ($gender === 'male') {
        if (!$isFemaleName) $score += 10;
        else $score -= 8;
    }

    $spec['score'] = $score;
    $scoredSpecs[] = $spec;
}

// Сортируем массив специалистов по убыванию баллов совместимости
usort($scoredSpecs, function ($a, $b) {
    return $b['score'] <=> $a['score'];
});

// Забираем победителя по скорингу
$bestMatch = $scoredSpecs[0];

if ($bestMatch && $bestMatch['score'] > -10) {
    echo json_encode([
        'success' => true,
        'id'      => $bestMatch['id'],
         'name'    => $bestMatch['first_name'] . ' ' . ($bestMatch['patronymic'] ?? '') . ' ' . $bestMatch['last_name'],
        'role'    => $bestMatch['specialization'],
        'img'     => $bestMatch['photo']
    ]);
} else {
    // Дефолтный фолбек, если никто не набрал проходной балл
    echo json_encode(['success' => false]);
}