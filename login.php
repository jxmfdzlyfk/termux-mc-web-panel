<?php
require_once __DIR__ . '/includes/auth.php';

start_session();

if (is_logged_in()) {
    header('Location: index.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pass = $_POST['password'] ?? '';
    if (verify_password($pass)) {
        $_SESSION['logged_in'] = true;
        $_SESSION['login_time'] = time();
        header('Location: index.php');
        exit;
    } else {
        $error = '密码错误';
    }
}
?>
<!DOCTYPE html>
<html lang="zh-CN" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>登录 - MC Web Panel</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body class="login-page">
    <div class="login-box">
        <h1>⛏️ MC Web Panel</h1>
        <p class="subtitle">Termux Minecraft 服务器管理</p>
        <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post">
            <input type="password" name="password" placeholder="请输入访问密码" required autofocus>
            <button type="submit">登录</button>
        </form>
        <button class="theme-toggle" onclick="toggleTheme()">🌓 切换主题</button>
    </div>
    <script>
        function toggleTheme() {
            const html = document.documentElement;
            const current = html.getAttribute('data-theme');
            const next = current === 'dark' ? 'light' : 'dark';
            html.setAttribute('data-theme', next);
            localStorage.setItem('theme', next);
        }
        const saved = localStorage.getItem('theme');
        if (saved) document.documentElement.setAttribute('data-theme', saved);
    </script>
</body>
</html>

