<?php
function start_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_name('MCPANEL_SESSION');
        session_start();
    }
}

function is_logged_in() {
    start_session();
    if (empty($_SESSION['logged_in'])) return false;
    // 2 小时无操作自动登出
    if (isset($_SESSION['last_active']) && time() - $_SESSION['last_active'] > 7200) {
        session_destroy();
        return false;
    }
    $_SESSION['last_active'] = time();
    return true;
}

function verify_password($pass) {
    $config = require __DIR__ . '/config.php';
    return password_verify($pass, $config['panel_password_hash']);
}

function require_login() {
    if (!is_logged_in()) {
        http_response_code(401);
        header('Content-Type: application/json');
        echo json_encode(['error' => '未登录']);
        exit;
    }
}
