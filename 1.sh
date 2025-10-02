#!/bin/bash

# PocketMine 自动安装程序（修正颜色版）
# 作者：Xiaoao
# 描述：自动下载并安装 PocketMine 服务器（支持本地插件目录）

# 定义变量
REPO_URL="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/archive/refs/heads/main.zip"
TEMP_ZIP="pocketmine_temp.zip"
TEMP_DIR="Termux-PocketMine0.14.x-Auto-Installer-main"
TARGET_DIR="$HOME/PocketMine"
START_SCRIPT="$HOME/PocketMine/start.sh"
PHP_BIN="$TARGET_DIR/bin/php"
PMMP_NAME="Genisys"
PMMP_VER="1.6dev"
PMMP_API="2.1.0"
PMMP_PHP="php7.0~7.3"
PHP_VER="7.2.8"
BACKTITLE="PocketMine 自动安装程序"

#检测是否有 dialog
#if command -v dialog &> /dev/null; then
#    echo "即将启动......"
#else
#    echo "未安装运行库 dialog 将自动安装..."
#    pkg install dialog
#if

# 初始化dialog
BACKTITLE="PocketMine 自动安装程序"

# Dialog颜色定义
TITLE_COLOR="\Z0\Zb" # 黑色粗体标题
#CONTENT_COLOR="\Z6"  # 青色内容文本

# 显示消息对话框（自适应大小）
show_msg() {
    dialog --backtitle "$BACKTITLE" \
           --colors \
           --title "${TITLE_COLOR}$1\Zn" \
           --msgbox "${CONTENT_COLOR}$2\Zn" 0 0
}

# 显示确认对话框（自适应大小）
show_yesno() {
    dialog --backtitle "$BACKTITLE" \
           --colors \
           --title "${TITLE_COLOR}$1\Zn" \
           --yesno "${CONTENT_COLOR}$2\Zn" 0 0
    return $?
}

# 显示输入对话框（自适应大小）
show_input() {
    dialog --backtitle "$BACKTITLE" \
           --colors \
           --title "${TITLE_COLOR}$1\Zn" \
           --inputbox "${CONTENT_COLOR}$2\Zn" 0 0 2>/tmp/input.$$
    result=$(cat /tmp/input.$$)
    rm -f /tmp/input.$$
    echo "$result"
}

# 显示欢迎信息
show_msg "欢迎" "欢迎使用 PocketMine 自动安装程序"

# 检测是否有PocketMine
if [ -d "$HOME/PocketMine" ]; then
    show_yesno "检测到已安装" "检测到你已经安装了PocketMine\n\n是否重装或者卸载？\n注意：重装会重置你的所有数据！"
    
    if [ $? -ne 0 ]; then
        # 用户选择否或取消
        clear
        exit 1
    fi
    
    # 提供选项
# 创建选项菜单
choice=$(dialog --menu "操作选择" 0 0 0 \
"r" "重装" \
"u" "卸载" \
"n" "退出" \
2>&1 >/dev/tty)

# 根据选择执行操作
case $choice in
    r)
        dialog --yesno "真的要重装吗？重装将失去服务器所有数据！" 0 0
        if [ $? -eq 0 ]; then
            echo "[*]清理旧文件..."
            rm -f "$TEMP_ZIP"
            rm -rf "$TARGET_DIR"
        else
            clear
            exit 1
        fi
        ;;
    u)
        confirm=$(dialog --inputbox "真的要卸载吗？卸载将失去服务器所有数据！\n请输入\"PocketMine\"确认卸载：" 0 0 2>&1 >/dev/tty)
        if [ "$confirm" == "PocketMine" ]; then
            rm -rf "$TARGET_DIR"
            dialog --msgbox "删除成功" 0 0
            clear
            exit 1
        else
            clear
            exit 1
        fi
        ;;
    *)
        clear
        exit 1  # 包括取消操作和退出选项
        ;;
esac

fi

# 询问版本信息
show_yesno "版本信息" "需要了解 PocketMine 版本信息吗？"
if [ $? -eq 0 ]; then
    version_info="PocketMine版本: $PMMP_NAME $PMMP_VER API $PMMP_API\nPHP版本: $PHP_VER"
    show_msg "版本详情" "$version_info"
fi

clear
# 下载并解压仓库
echo "正在下载安装包..."
curl -L -o "$TEMP_ZIP" "$REPO_URL"

echo "正在解压文件..."
unzip -q -o "$TEMP_ZIP" -d "$HOME"
mv "$HOME/$TEMP_DIR" "$TARGET_DIR"

# 处理插件目录
if [ -d "$TARGET_DIR/plugins" ]; then
    show_yesno "插件目录" "检测到插件目录，是否保留？"
    if [ $? -ne 0 ]; then
        rm -rf "$TARGET_DIR/plugins"
    fi
fi

clear
# 添加执行权限
echo "正在设置执行权限..."
chmod +x "$START_SCRIPT"
chmod +x "$PHP_BIN"

# 配置快捷启动命令
echo "正在配置快捷启动命令(mc)..."

# 配置bash
if ! grep -q 'alias mc=' "$HOME/.bashrc"; then
    echo "alias mc='$START_SCRIPT'" >> "$HOME/.bashrc"
    echo "Bash配置" "Bash别名已添加"
else
    echo "Bash配置" "Bash别名已存在，跳过添加"
fi

# 配置fish
if [ -d "$HOME/.config/fish" ] && [ -f "$HOME/.config/fish/config.fish" ]; then
    if ! grep -q 'alias mc=' "$HOME/.config/fish/config.fish"; then
        echo "alias mc='$START_SCRIPT'" >> "$HOME/.config/fish/config极速启动脚本.fish"
        echo "Fish配置" "Fish别名已添加"
    else
        echo "Fish配置" "Fish别名已存在，跳过添加"
    fi
else
    echo "Fish配置" "未找到Fish配置文件，跳过别名配置"
fi

echo "正在删除缓存文件..."
rm -f "$TEMP_ZIP"

# 启动服务器
show_yesno "启动服务器" "是否立即启动 PocketMine 服务器？"
if [ $? -eq 0 ]; then
    show_msg "启动" "正在启动 PocketMine 服务器..."
    clear
    "$START_SCRIPT"
fi

show_msg "完成" "配置已生效，现在可以使用 'mc' 命令启动服务器\n感谢您的使用！"

# 重新加载配置
show_msg "重载配置" "正在重新加载配置..."
if [ -f "$HOME/.bashrc" ]; then
    source "$HOME/.bashrc"
fi

if [ -f "$HOME/.config/fish/config.fish" ]; then
    fish -c 'source ~/.config/fish/config.fish'
fi

exit 0
