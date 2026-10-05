#!/data/data/com.termux/files/usr/bin/bash
# 卸载 Web 面板

set -euo pipefail

PANEL_DIR="$PREFIX/share/nginx/html/mcpanel"

GREEN='\033[0;32m'; YELLOW='\033[1;33m'; NC='\033[0m'

echo ""
echo -e "${YELLOW}即将删除: $PANEL_DIR${NC}"
read -p "确认卸载？[y/N]: " confirm

if [[ ! "${confirm:-}" =~ ^[Yy]$ ]]; then
    echo "已取消"
    exit 0
fi

if [ -d "$PANEL_DIR" ]; then
    rm -rf "$PANEL_DIR"
    echo -e "${GREEN}已删除 Web 面板${NC}"
else
    echo "Web 面板未安装"
fi

echo ""
echo "如需停止 Nginx / PHP-FPM:"
echo "  pkill nginx"
echo "  pkill php-fpm"

