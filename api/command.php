<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/rcon.php';

require_login();

$config = require __DIR__ . '/../includes/config.php';

$input = json_decode(file_get_contents('php://input'), true);
$cmd = trim($input['command'] ?? '');

if ($cmd === '') {
    json_response(['error' => '命令不能为空'], 400);
}

if (!is_server_running($config['server_dir'])) {
    json_response(['error' => '服务器未运行'], 400);
}

// 危险命令过滤（防止面板被滥用关机/删档）
$dangerous = ['/stop', 'stop'];
// 允许 stop（面板上有专门按钮），其他危险命令可扩展

try {
    $rcon = new Rcon();
    $rcon->connect($config['rcon_host'], $config['rcon_port'], $config['rcon_password'], 3);

    // 去掉开头的 /
    $cmd = ltrim($cmd, '/');
    $response = $rcon->command($cmd);
    $rcon->close();

    json_response(['success' => true, 'response' => $response ?: '(无输出)']);
} catch (Exception $e) {
    json_response(['error' => 'RCON 执行失败: ' . $e->getMessage()], 500);
}
