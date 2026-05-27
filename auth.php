<?php //auth.php
session_start();
require_once 'db.php';
$action = $_GET['action'] ?? '';

// Функция для красивых уведомлений
function redirectWithToast($url, $msg, $isError = false) {
    $errStr = $isError ? 'true' : 'false';
    echo "<script>
        localStorage.setItem('flashToast', JSON.stringify({msg: '$msg', isError: $errStr}));
        window.location.href='$url';
    </script>";
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // РЕГИСТРАЦИЯ
    if (isset($_POST['register'])) {
        $surname = trim($_POST['surname']);
        $name = trim($_POST['name']);
        $patronymic = trim($_POST['patronymic']);
        $email = trim($_POST['email']);
        $raw_password = $_POST['password'];

        // ПРОВЕРКА НА ДЛИНУ ПАРОЛЯ
        if (strlen($raw_password) < 6) {
            redirectWithToast('index.php', 'Ошибка: Пароль должен быть не менее 6 символов!', true);
        }

        $pass = password_hash($raw_password, PASSWORD_DEFAULT);
        
        try {
            $stmt = $pdo->prepare("INSERT INTO users (surname, name, patronymic, email, password) VALUES (?, ?, ?, ?, ?)");
            $stmt->execute([$surname, $name, $patronymic, $email, $pass]);
            
            $_SESSION['user_id'] = $pdo->lastInsertId();
            redirectWithToast('cabinet.php', 'Регистрация прошла успешно!', false);
        } catch (PDOException $e) {
            redirectWithToast('index.php', 'Ошибка регистрации: Email уже занят', true);
        }
    } 
    // ВХОД 
    elseif (isset($_POST['login'])) {
        $email = $_POST['email'];
        $password = $_POST['password'];
        
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            
            // Принудительно задаем вкладку по умолчанию в JS через localStorage перед редиректом
            $defaultTab = ($user['role'] === 'admin') ? 'admin-appointments' : (($user['role'] === 'psychologist') ? 'psych-appointments' : 'sessions');
            
            echo "<script>
                localStorage.setItem('activeCabinetTab', '$defaultTab');
                localStorage.setItem('flashToast', JSON.stringify({msg: 'Вы успешно вошли в систему!', isError: false}));
                window.location.href='cabinet.php';
            </script>";
            exit;
        }
    }
}

if ($action === 'logout') {
    session_destroy();
    header("Location: index.php");
    exit;
}
?>