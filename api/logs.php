<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';

require_login();

$config = require __DIR__ . '/../includes/config.php';

$log_file = $config['log_file'];

// 尝试多个候选路径
$candidates = [
    $log_file,
    $config['server_dir'] . '/logs/latest.log',
    $config['server_dir'] . '/server.log',
];

$content = "找不到日志文件";
foreach ($candidates as $file) {
    if (file_exists($file)) {
        $content = tail_log($file, 100);
        break;
    }
}

json_response(['logs' => $content]);
