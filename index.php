<?php
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/helpers.php';

start_session();
if (!is_logged_in()) {
    header('Location: login.php');
    exit;
}

$config = require __DIR__ . '/includes/config.php';
$server_dir = $config['server_dir'];
?>
<!DOCTYPE html>
<html lang="zh-CN" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MC Web Panel</title>
    <link rel="stylesheet" href="assets/style.css">
</head>
<body>
    <header class="topbar">
        <div class="brand">⛏️ MC Web Panel</div>
        <div class="actions">
            <button class="theme-toggle" onclick="toggleTheme()" title="切换主题">🌓</button>
            <a href="logout.php" class="btn btn-ghost">退出</a>
        </div>
    </header>

    <main class="container">
        <section class="card status-card">
            <div class="status-header">
                <h2>服务器状态</h2>
                <span id="status-badge" class="badge badge-unknown">检测中...</span>
            </div>
            <div class="status-grid">
                <div><span class="label">版本</span><span id="server-version">-</span></div>
                <div><span class="label">在线玩家</span><span id="online-players">-</span></div>
                <div><span class="label">运行时间</span><span id="uptime">-</span></div>
                <div><span class="label">TPS</span><span id="tps">-</span></div>
            </div>
            <div class="controls">
                <button class="btn btn-success" onclick="doAction('start')">▶ 启动</button>
                <button class="btn btn-warning" onclick="doAction('restart')">🔄 重启</button>
                <button class="btn btn-danger" onclick="doAction('stop')">⏹ 停止</button>
            </div>
        </section>

        <section class="card">
            <h2>控制台</h2>
            <div class="console-output" id="console-output">等待连接...</div>
            <div class="console-input">
                <input type="text" id="cmd-input" placeholder="输入命令，例如: say Hello" onkeydown="if(event.key==='Enter')sendCommand()">
                <button class="btn btn-primary" onclick="sendCommand()">发送</button>
            </div>
        </section>

        <section class="card">
            <div class="logs-header">
                <h2>实时日志</h2>
                <label class="switch">
                    <input type="checkbox" id="auto-scroll" checked> 自动滚动
                </label>
            </div>
            <pre class="logs-output" id="logs-output">加载中...</pre>
        </section>
    </main>

    <script>
        window.PANEL_CONFIG = {
            serverDir: <?= json_encode($server_dir) ?>
        };
    </script>
    <script src="assets/app.js"></script>
</body>
</html>

