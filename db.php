<?php //db.php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = '127.0.0.1';
$db   = 'joy_db';
$user = 'root'; 
$pass = 'root'; // ТВОЙ ПАРОЛЬ!
$charset = 'utf8mb4';
$port = '8889'; 
$dsn = "mysql:host=$host;port=$port;dbname=$db;charset=$charset";
$opt = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
    $pdo = new PDO($dsn, $user, $pass, $opt);
} catch (\PDOException $e) {
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}

// Создание админа по умолчанию
$stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
$stmt->execute(['admin@joy.com']);
if ($stmt->fetchColumn() == 0) {
    $hash = password_hash('admin123', PASSWORD_DEFAULT);
    $sql = "INSERT INTO users (name, email, password, role) VALUES ('Admin', 'admin@joy.com', '$hash', 'admin')";
    $pdo->exec($sql);
}

// Хелпер для получения текущего юзера
function getCurrentUser($pdo) {
    if (isset($_SESSION['user_id'])) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$_SESSION['user_id']]);
        return $stmt->fetch();
    }
    return null;
}

// Хелпер для получения настроек сайта (контактов)
function getSettings($pdo) {
    try {
        $stmt = $pdo->query("SELECT * FROM settings LIMIT 1");
        $settings = $stmt->fetch();
        if ($settings) return $settings;
    } catch (Exception $e) {}
    // Если таблицы еще нет, возвращаем пустые значения во избежание ошибок
    return ['phone' => '', 'email' => '', 'address' => '', 'telegram' => '', 'instagram' => '', 'work_hours' => ''];
}
?>