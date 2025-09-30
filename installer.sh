#!/bin/bash

# PocketMine 自动安装程序
# 作者：Xiaoao
# 描述：自动下载并安装 PocketMine 服务器（支持本地插件目录）

# 定义变量
REPO_URL="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/archive/refs/heads/main.zip"
TEMP_ZIP="pocketmine_temp.zip"
TEMP_DIR="Termux-PocketMine0.14.x-Auto-Installer-main"
TARGET_DIR="$HOME/PocketMine"
START_SCRIPT="$TARGET_DIR/start.sh"
PHP_BIN="$TARGET_DIR/bin/php"
PMMP_NAME="Genisys"
PMMP_VER="1.6dev"
PMMP_API="2.1.0"
PMMP_PHP="php7.0~7.3"
PHP_VER="7.2.8"

# 显示欢迎信息
echo "[*]欢迎使用 PocketMine 自动安装程序"
read -p "按回车键继续..."

#检测是否有PocketMine
if [ -d "$HOME/PocketMine" ]; then
    echo "检测到你已经安装了PocketMine"
    echo "是否重装或者卸载？"
    echo "注意：重装会重置你的所有数据！"
    read -p "(r-重装 N-退出 u-卸载: )" choice_PMMP
    if [[ -z "$choice_PMMP" || "$choice_PMMP" =~ ^[Nn]$ ]] ; then
        exit 1
    elif [[ "$choice_PMMP" =~ ^[Rr]$ ]] ; then
        read -p "真的要重装吗？重装将失去服务器所有数据！可输入Ctrl+C关闭程序"
        echo "[*]清理旧文件..."
        rm -f "$TEMP_ZIP"
        rm -rf "$TARGET_DIR"
    elif [[ "$choice_PMMP" =~ ^[Uu]$ ]] ; then
        read -p "真的要卸载吗？重装将失去服务器所有数据！可输入Ctrl+C关闭程序"
        read -p "如果要卸载，请输入\"PocketMine\"" PocketMine_read
        if [ $PocketMine_read == "PocketMine" ] ; then
            rm -rf "$TARGET_DIR"
            echo "删除成功"
            exit 1
        else
            exit 1
        fi
    else
        exit 1
    fi
fi
# 询问版本信息
read -p "[*]需要了解 PocketMine 版本信息吗？(y/N) " choice
case "$choice" in
    [Yy]*)  
        echo "PocketMine版本: $PMMP_NAME $PMMP_VER API $PMMP_API"
        echo "PHP版本: $PHP_VER"
        read -p "按回车键开始安装..." 
        ;;
    *)  
        echo "即将开始安装..."
        sleep 0.5
        ;;
esac

# 清理旧文件
#echo "[*]清理旧文件..."
#rm -f "$TEMP_ZIP"
#rm -rf "$TARGET_DIR"

# 下载并解压仓库
echo "[*]下载安装包..."
curl -L -o "$TEMP_ZIP" "$REPO_URL"
echo "[*]解压文件..."
unzip -q -o "$TEMP_ZIP" -d "$HOME"
mv "$HOME/$TEMP_DIR" "$TARGET_DIR"

# 处理插件（保留原有逻辑）
echo "[*]处理插件目录..."
if [ -d "$TARGET_DIR/plugins" ]; then
    read -p "[*]检测到插件目录，是否保留？(y/N) " plugin_choice
    if [[ ! $plugin_choice =~ ^[Yy]$ ]]; then
        rm -rf "$TARGET_DIR/plugins"
    fi
fi

# 添加执行权限
echo "[*]设置执行权限..."
chmod +x "$START_SCRIPT"
chmod +x "$PHP_BIN"

# 强制配置快捷启动命令（无交互）
echo "[*]配置快捷启动命令(mc)..."
sleep 0.2
# 配置bash（带存在性检查）
echo "正在配置Bash别名..."
if ! grep -q 'alias mc=' "$HOME/.bashrc"; then
    echo "alias mc='$START_SCRIPT'" >> "$HOME/.bashrc"
    echo "[*]Bash别名已添加"
else
    echo "[*]Bash别名已存在，跳过添加"
fi

# 配置fish（带存在性检查）
echo "正在配置Fish别名..."
if [ -d "$HOME/.config/fish" ] && [ -f "$HOME/.config/fish/config.fish" ]; then
    if ! grep -q 'alias mc=' "$HOME/.config/fish/config.fish"; then
        echo "alias mc='$START_SCRIPT'" >> "$HOME/.config/fish/config.fish"
        echo "[*]Fish别名已添加"
    else
        echo "[*]Fish别名已存在，跳过添加"
    fi
else
    echo "[*]未找到Fish配置文件，跳过别名配置"
fi

echo "删除缓存文件"
rm -f "$TEMP_ZIP"


# 启动服务器（前台运行）
echo "[*]启动 PocketMine 服务器..."
echo "[*]服务器已启动"
"$START_SCRIPT"

echo "[*]配置已生效，现在可以使用 'mc' 命令启动服务器"
echo "感谢您的使用！"
read -p "换行以进行重载..."

# 服务器停止后重新加载配置
echo "[*]服务器已停止，正在重新加载配置..."
# 重新加载Bash配置
if [ -f "$HOME/.bashrc" ]; then
    echo "[*]重新加载Bash配置..."
    source "$HOME/.bashrc"
fi

# 检测并重新加载Fish配置
if [ -f "$HOME/.config/fish/config.fish" ]; then
    echo "[*]重新加载Fish配置..."
    fish -c 'source ~/.config/fish/config.fish'
fi

