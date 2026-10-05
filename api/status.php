<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/rcon.php';

require_login();

$config = require __DIR__ . '/../includes/config.php';

$status = [
    'running' => is_server_running($config['server_dir']),
    'pid' => get_server_pid($config['server_dir']),
    'version' => '-',
    'online' => '-',
    'tps' => '-',
    'uptime' => '-',
];

// 尝试通过 RCON 获取更多信息
if ($status['running']) {
    try {
        $rcon = new Rcon();
        $rcon->connect($config['rcon_host'], $config['rcon_port'], $config['rcon_password'], 2);

        // 获取在线玩家
        $list = $rcon->command('list');
        if (preg_match('/There are (\d+) of a max of (\d+)/', $list, $m)) {
            $status['online'] = $m[1] . ' / ' . $m[2];
        } elseif (preg_match('/有 (\d+) 名玩家/', $list, $m)) {
            $status['online'] = $m[1];
        }

        // 尝试获取 TPS
        $tps = $rcon->command('tps');
        if (preg_match('/TPS from last.*?:\s*([\d.]+)/s', $tps, $m)) {
            $status['tps'] = $m[1];
        }

        $rcon->close();
    } catch (Exception $e) {
        // RCON 未启用，忽略
    }
}

json_response($status);
