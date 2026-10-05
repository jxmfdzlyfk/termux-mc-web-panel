<?php
/**
 * 检测服务器进程是否运行
 */
function is_server_running($server_dir) {
    $output = shell_exec('pgrep -f "server\.jar|nukkit\.jar" 2>/dev/null');
    return !empty(trim($output ?? ''));
}

/**
 * 获取服务器进程 PID
 */
function get_server_pid($server_dir) {
    $output = shell_exec('pgrep -f "server\.jar|nukkit\.jar" 2>/dev/null | head -1');
    return trim($output ?? '');
}

/**
 * 安全执行 shell 命令（白名单 + 转义）
 */
function safe_exec($cmd) {
    return shell_exec($cmd . ' 2>&1');
}

/**
 * JSON 响应
 */
function json_response($data, $code = 200) {
    http_response_code($code);
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

/**
 * 获取日志文件最后 N 行
 */
function tail_log($file, $lines = 100) {
    if (!file_exists($file)) return "日志文件不存在: $file";
    $content = @file_get_contents($file);
    if ($content === false) return "无法读取日志";
    $all = explode("\n", $content);
    $slice = array_slice($all, -$lines);
    return implode("\n", $slice);
}
