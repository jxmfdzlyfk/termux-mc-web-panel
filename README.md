# Termux Minecraft Web Panel

为 [termux-mcserver-install-shell](https://github.com/jxmfdzlyfk/termux-mcserver-install-shell) 安装的 Minecraft 服务器提供的 Web 管理面板。

![Version](https://img.shields.io/badge/version-v0.1.0-blue)
![License](https://img.shields.io/badge/license-MIT-green)

## ✨ 特性

- 📊 实时服务器状态（运行/停止、在线玩家、TPS）
- 🎮 一键启动 / 停止 / 重启
- 💻 Web 控制台，直接执行服务器命令
- 📜 实时日志查看
- 🌓 暗色 / 亮色主题切换
- 📱 手机和电脑都适配

## 📱 环境要求

- [Termux](https://f-droid.org/packages/com.termux/)（从 F-Droid 安装）
- 已完成主仓库 `termux-mcserver-install-shell` 的服务器安装
- Nginx + PHP + PHP-FPM

## 🚀 快速开始

### 一键安装

```bash
git clone https://github.com/jxmfdzlyfk/termux-mc-web-panel
cd termux-mc-web-panel
bash install.sh
```

安装脚本会：
1. 安装 Nginx + PHP + PHP-FPM（如未安装）
2. 询问你的 Minecraft 服务器目录
3. 自动配置 RCON（在 `server.properties` 中启用）
4. 部署 Web 文件到 `$PREFIX/share/nginx/html/mcpanel`
5. 生成配置文件（包含你的密码）
6. 启动 Nginx 和 PHP-FPM

### 访问面板

- 本地: `http://localhost:8080/mcpanel/`
- 局域网: `http://<你的手机IP>:8080/mcpanel/`

登录密码是安装时你设置的密码。

## 🎯 使用说明

### 服务器操作

- **▶ 启动**：后台运行 `start.sh`
- **⏹ 停止**：优先通过 RCON 发送 `stop` 优雅关闭
- **🔄 重启**：先停止再启动

### 控制台

在输入框中输入命令（不需要 `/` 前缀），按回车或点击"发送"：
```
say Hello World
gamemode creative xiaotianquan
whitelist add Steve
```

### 日志

面板每 3 秒自动刷新最新 100 行日志。

## ⚠️ 注意事项

- **RCON 端口**：默认 `25575`，如被占用可修改 `server.properties`
- **安全性**：面板默认只监听 `8080`，局域网访问。**不要暴露到公网**。
- **密码丢失**：删除 `$PREFIX/share/nginx/html/mcpanel/includes/config.php` 后重新运行 `install.sh`
- **性能影响**：Web 面板本身很轻量，但 PHP-FPM 会占用一些内存（约 20MB）

## 🗑️ 卸载

```bash
bash uninstall.sh
```

## 📋 更新日志

### v0.1.0 (2026-10-04)
- 首次发布
- 登录鉴权
- 服务器状态监控
- 启动/停止/重启
- Web 控制台
- 实时日志
- 暗色/亮色主题

## 🐛 反馈

提交 [Issue](https://github.com/jxmfdzlyfk/termux-mc-web-panel/issues)，附上：
- 服务器类型和版本
- Nginx / PHP-FPM 状态
- 具体错误信息

## 📜 开源协议

MIT License
