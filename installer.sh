#!/bin/bash

# 定义仓库信息
REPO_URL="https://github.com/termux-lab/Termux-PocketMine0.14.x-Auto-Installer"
TARGET_DIR="$HOME/Termux-PocketMine0.14.x-Auto-Installer"
TEMP_DIR=$(mktemp -d)

# 清理函数
cleanup() {
    echo "正在清理临时文件..."
    rm -rf "$TEMP_DIR"
}
trap cleanup EXIT

# 检查必要工具
check_dependencies() {
    if ! command -v git &> /dev/null; then
        echo "错误：需要安装git，但未找到。"
        echo "请使用以下命令安装："
        echo "  Ubuntu/Debian: sudo apt install git"
        echo "  CentOS/RHEL: sudo yum install git"
        exit 1
    fi
}

# 用户确认函数
confirm_download() {
    while true; do
        read -p "是否要下载并安装 Termux PocketMine0.14.x? [y/n]: " choice
        case "$choice" in
            [Yy]* ) 
                echo "开始下载..."
                return 0
                ;;
            [Nn]* )
                echo "安装已取消"
                exit 0
                ;;
            * ) 
                echo "请输入 y 或 n"
                ;;
        esac
    done
}

# 主函数
main() {
    check_dependencies
    confirm_download

    echo "正在克隆仓库..."
    if git clone --depth 1 "$REPO_URL" "$TEMP_DIR"; then
        echo "[*]仓库下载成功"
    else
        echo "[*]下载失败，请检查网络连接和仓库URL"
        echo "[*]请尝试使用VPN后重新执行"
        exit 1
    fi

    # 移动文件到目标目录
    echo "正在移动文件到目标目录: $TARGET_DIR"
    rm -rf "$TARGET_DIR" 2>/dev/null
    mv "$TEMP_DIR" "$TARGET_DIR"

    # 检查start.sh文件
    START_FILE="$TARGET_DIR/start.sh"
    if [[ -f "$START_FILE" ]]; then
        echo "找到启动脚本，添加执行权限..."
        chmod +x "$START_FILE"
        
        echo "正在执行启动脚本..."
        cd "$TARGET_DIR" || exit
        ./start.sh
        exit 1
    else
        echo "❌ 错误：未找到 start.sh 文件"
        echo "目录内容："
        ls -l "$TARGET_DIR"
        exit 1
    fi
}

main