// 主题切换
function toggleTheme() {
    const html = document.documentElement;
    const current = html.getAttribute('data-theme');
    const next = current === 'dark' ? 'light' : 'dark';
    html.setAttribute('data-theme', next);
    localStorage.setItem('theme', next);
}
(function() {
    const saved = localStorage.getItem('theme');
    if (saved) document.documentElement.setAttribute('data-theme', saved);
})();

// ===== API 调用 =====
async function api(path, options = {}) {
    const res = await fetch(path, {
        headers: { 'Content-Type': 'application/json' },
        ...options
    });
    return res.json();
}

// ===== 状态刷新 =====
async function refreshStatus() {
    try {
        const data = await api('api/status.php');
        const badge = document.getElementById('status-badge');
        if (data.running) {
            badge.className = 'badge badge-online';
            badge.textContent = '运行中 (PID: ' + (data.pid || '?') + ')';
        } else {
            badge.className = 'badge badge-offline';
            badge.textContent = '已停止';
        }
        document.getElementById('server-version').textContent = data.version || '-';
        document.getElementById('online-players').textContent = data.online || '-';
        document.getElementById('tps').textContent = data.tps || '-';
    } catch (e) {
        console.error('状态刷新失败', e);
    }
}

// ===== 服务器操作 =====
async function doAction(action) {
    const labels = { start: '启动', stop: '停止', restart: '重启' };
    if (!confirm(`确认${labels[action]}服务器？`)) return;

    const buttons = document.querySelectorAll('.controls .btn');
    buttons.forEach(b => b.disabled = true);

    try {
        const data = await api('api/action.php', {
            method: 'POST',
            body: JSON.stringify({ action })
        });
        if (data.error) {
            alert('错误: ' + data.error);
        } else {
            alert(data.message || '操作已发送');
            setTimeout(refreshStatus, 1500);
        }
    } catch (e) {
        alert('请求失败: ' + e.message);
    } finally {
        buttons.forEach(b => b.disabled = false);
    }
}

// ===== 控制台命令 =====
async function sendCommand() {
    const input = document.getElementById('cmd-input');
    const cmd = input.value.trim();
    if (!cmd) return;

    const output = document.getElementById('console-output');
    output.textContent += `\n> ${cmd}\n`;

    try {
        const data = await api('api/command.php', {
            method: 'POST',
            body: JSON.stringify({ command: cmd })
        });
        if (data.error) {
            output.textContent += `[错误] ${data.error}\n`;
        } else {
            output.textContent += `${data.response}\n`;
        }
    } catch (e) {
        output.textContent += `[请求失败] ${e.message}\n`;
    }
    output.scrollTop = output.scrollHeight;
    input.value = '';
}

// ===== 日志刷新 =====
async function refreshLogs() {
    try {
        const data = await api('api/logs.php');
        const el = document.getElementById('logs-output');
        if (el) {
            el.textContent = data.logs || '(空)';
            if (document.getElementById('auto-scroll')?.checked) {
                el.scrollTop = el.scrollHeight;
            }
        }
    } catch (e) {
        console.error('日志刷新失败', e);
    }
}

// ===== 定时刷新 =====
refreshStatus();
refreshLogs();

setInterval(refreshStatus, 5000);   // 状态每 5 秒
setInterval(refreshLogs, 3000);     // 日志每 3 秒
