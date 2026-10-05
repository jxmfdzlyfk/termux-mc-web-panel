<?php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/helpers.php';
require_once __DIR__ . '/../includes/rcon.php';

require_login();

$config = require __DIR__ . '/../includes/config.php';

$input = json_decode(file_get_contents('php://input'), true);
$action = $input['action'] ?? '';

if (!in_array($action, ['start', 'stop', 'restart'], true)) {
    json_response(['error' => '无效操作'], 400);
}

$server_dir = $config['server_dir'];
$start_script = $config['start_script'];

try {
    switch ($action) {
        case 'start':
            if (is_server_running($server_dir)) {
                json_response(['error' => '服务器已在运行'], 400);
            }
            // 后台启动
            $log = escapeshellarg("$server_dir/webpanel_start.log");
            $cmd = "cd " . escapeshellarg($server_dir) . " && nohup bash " . escapeshellarg($start_script) . " > $log 2>&1 &";
            shell_exec($cmd);
            json_response(['success' => true, 'message' => '启动命令已发送']);
            break;

        case 'stop':
            if (!is_server_running($server_dir)) {
                json_response(['error' => '服务器未运行'], 400);
            }
            // 优先 RCON 优雅停止
            $stopped = false;
            try {
                $rcon = new Rcon();
                $rcon->connect($config['rcon_host'], $config['rcon_port'], $config['rcon_password'], 3);
                $rcon->command('stop');
                $rcon->close();
                $stopped = true;
            } catch (Exception $e) {
                // RCON 失败，尝试直接 kill
            }
            if (!$stopped) {
                shell_exec('pkill -f "server\.jar|nukkit\.jar"');
            }
            json_response(['success' => true, 'message' => '停止命令已发送']);
            break;

        case 'restart':
            if (is_server_running($server_dir)) {
                try {
                    $rcon = new Rcon();
                    $rcon->connect($config['rcon_host'], $config['rcon_port'], $config['rcon_password'], 3);
                    $rcon->command('stop');
                    $rcon->close();
                } catch (Exception $e) {
                    shell_exec('pkill -f "server\.jar|nukkit\.jar"');
                }
                // 等待进程退出
                for ($i = 0; $i < 30; $i++) {
                    if (!is_server_running($server_dir)) break;
                    sleep(1);
                }
            }
            // 启动
            $log = escapeshellarg("$server_dir/webpanel_start.log");
            $cmd = "cd " . escapeshellarg($server_dir) . " && nohup bash " . escapeshellarg($start_script) . " > $log 2>&1 &";
            shell_exec($cmd);
            json_response(['success' => true, 'message' => '重启命令已发送']);
            break;
    }
} catch (Exception $e) {
    json_response(['error' => $e->getMessage()], 500);
}
