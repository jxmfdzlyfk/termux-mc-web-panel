#!/data/data/com.termux/files/usr/bin/bash
#============================================================
# Termux Minecraft Web Panel - 安装脚本
# 版本: v0.1.0
#============================================================

set -euo pipefail

PANEL_DIR="$PREFIX/share/nginx/html/mcpanel"
NGINX_CONF="$PREFIX/etc/nginx/nginx.conf"
PHP_FPM_CONF="$PREFIX/etc/php-fpm.d/www.conf"

GREEN='\033[0;32m'; YELLOW='\033[1;33m'; RED='\033[0;31m'; CYAN='\033[0;36m'; NC='\033[0m'
info() { echo -e "${GREEN}[信息]${NC} $1"; }
warn() { echo -e "${YELLOW}[警告]${NC} $1"; }
error() { echo -e "${RED}[错误]${NC} $1"; exit 1; }

echo ""
echo -e "${CYAN}=========================================="
echo "  Minecraft Web Panel 安装 (v0.1.0)"
echo -e "==========================================${NC}"
echo ""

# 1. 检查环境
[ -d "/data/data/com.termux" ] || error "此脚本只能在 Termux 中运行"

# 2. 安装依赖
info "检查依赖..."
for pkg in nginx php php-fpm; do
    if ! pkg list-installed 2>/dev/null | grep -q "^${pkg}/"; then
        info "安装 $pkg..."
        pkg install -y "$pkg"
    fi
done

# 3. 输入服务器目录
echo ""
info "请输入要管理的 Minecraft 服务器目录"
echo "  例如: $HOME/mcserver_paper_1.21.1"
read -p "服务器目录: " SERVER_DIR

[ -d "$SERVER_DIR" ] || error "目录不存在: $SERVER_DIR"
[ -f "$SERVER_DIR/start.sh" ] || error "找不到 start.sh，请先运行主安装脚本"

SERVER_DIR=$(cd "$SERVER_DIR" && pwd)

# 4. 输入面板密码
echo ""
read -s -p "设置 Web 面板访问密码: " PANEL_PASS
echo ""
[ -n "$PANEL_PASS" ] || error "密码不能为空"

# 5. 输入 RCON 密码
echo ""
RCON_PASS=$(head -c 32 /dev/urandom | base64 | tr -d '/+=' | head -c 24)
info "已自动生成 RCON 密码: $RCON_PASS"
info "（记下这个密码，忘记可在 server.properties 里查看）"

# 6. 配置 server.properties 启用 RCON
SERVER_PROPS="$SERVER_DIR/server.properties"
if [ -f "$SERVER_PROPS" ]; then
    info "配置 server.properties 启用 RCON..."
    sed -i '/^enable-rcon=/d;/^rcon.port=/d;/^rcon.password=/d' "$SERVER_PROPS" 2>/dev/null || true
    {
        echo "enable-rcon=true"
        echo "rcon.port=25575"
        echo "rcon.password=$RCON_PASS"
    } >> "$SERVER_PROPS"
    info "已启用 RCON (端口 25575)"
else
    warn "找不到 server.properties，RCON 将无法使用"
    warn "请先启动一次服务器生成配置，再运行本脚本"
fi

# 7. 部署 Web 文件
info "部署 Web 面板到 $PANEL_DIR"
mkdir -p "$PANEL_DIR"
SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cp -r "$SCRIPT_DIR"/* "$PANEL_DIR/" 2>/dev/null || true
rm -f "$PANEL_DIR/install.sh" "$PANEL_DIR/uninstall.sh"

# 8. 生成 config.php
PASS_HASH=$(php -r "echo password_hash('$PANEL_PASS', PASSWORD_DEFAULT);")
cat > "$PANEL_DIR/includes/config.php" <<EOF
<?php
return [
    'panel_password_hash' => '$PASS_HASH',
    'server_dir'          => '$SERVER_DIR',
    'server_type'         => '${SERVER_TYPE:-auto}',
    'rcon_host'           => '127.0.0.1',
    'rcon_port'           => 25575,
    'rcon_password'       => '$RCON_PASS',
    'log_file'            => '$SERVER_DIR/logs/latest.log',
    'start_script'        => '$SERVER_DIR/start.sh',
];
EOF
chmod 600 "$PANEL_DIR/includes/config.php"
info "配置文件已生成"

# 9. 配置 Nginx（PHP 支持）
info "配置 Nginx..."
if ! grep -q 'fastcgi_pass' "$NGINX_CONF" 2>/dev/null; then
    warn "Nginx 尚未配置 PHP 支持，正在自动配置..."
    cp "$NGINX_CONF" "${NGINX_CONF}.bak.$(date +%Y%m%d_%H%M%S)"

    cat > "$NGINX_CONF" <<'NGINX_EOF'
events { worker_connections 1024; }
http {
    include mime.types;
    default_type application/octet-stream;
    sendfile on;
    keepalive_timeout 65;

    server {
        listen 8080;
        server_name localhost;
        root /data/data/com.termux/files/usr/share/nginx/html;
        index index.php index.html;

        location / {
            try_files $uri $uri/ =404;
        }

        location ~ \.php$ {
            fastcgi_pass unix:/data/data/com.termux/files/usr/var/run/php-fpm.sock;
            fastcgi_index index.php;
            fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
            include fastcgi_params;
        }
    }
}
NGINX_EOF
    info "Nginx 配置已生成"
fi

# 10. 启动服务
info "启动 PHP-FPM..."
pkill php-fpm 2>/dev/null || true
sleep 1
php-fpm

info "启动 Nginx..."
pkill nginx 2>/dev/null || true
sleep 1
nginx

# 11. 完成
LOCAL_IP=$(ifconfig 2>/dev/null | grep -A1 'wlan0' | grep 'inet' | awk '{print $2}' | head -1)
echo ""
echo -e "${GREEN}=========================================="
echo "  安装完成！"
echo -e "==========================================${NC}"
echo ""
echo "  访问地址（本地）: http://localhost:8080/mcpanel/"
[ -n "$LOCAL_IP" ] && echo "  访问地址（局域网）: http://${LOCAL_IP}:8080/mcpanel/"
echo "  登录密码: (你刚才设置的密码)"
echo ""
echo "  ⚠️  首次访问前，建议先启动一次 Minecraft 服务器"
echo "     让 server.properties 和 logs/ 目录生成完整"
echo ""
echo "  常用命令:"
echo "    nginx -s reload     # 重载 Nginx"
echo "    pkill php-fpm       # 停止 PHP-FPM"
echo "    php-fpm             # 启动 PHP-FPM"
echo ""
