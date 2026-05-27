<?php //functions.php
//  Дата и время
function getJoyDate() {
    $months = [1=>'Янв','Фев','Мар','Апр','Май','Июн','Июл','Авг','Сен','Окт','Ноя','Дек'];
    return date('d') . ' ' . $months[date('n')] . ' ' . date('Y');
}

// Календарь 
function drawMiniCalendar() {
    $d = date('d');
    return "<div style='font-size:0.8rem; text-align:center; margin-top:10px; color:#888;'>
            Сегодня: <span style='color:#E0C6AD; font-weight:bold;'>".getJoyDate()."</span>
            </div>";
}

// Файлы (Список картинок для админа)
function getImagesFromDir() {
    $files = scandir(__DIR__ . '/img');
    $res = [];
    foreach($files as $f) {
        if($f!='.' && $f!='..') $res[] = $f;
    }
    return array_slice($res, 0, 5);
}
?>