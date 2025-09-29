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
echo "[*]欢迎来到这个安装程序"
read -p "按回车继续..." 

# 询问用户是否需要查看版本信息
read -p "[*]需要了解这个安装程序所用的PocketMine版本和php版本吗？(y/N) " choice

# 处理用户输入
case "$choice" in
    [Yy]*)  
        echo "PocketMine版本: $PMMP_NAME $PMMP_VER API $PMMP_API"
        echo "PocketMine支持的php版本: $PMMP_PHP"
        echo "此安装程序所用版本: $PHP_VER"
        read -p "按回车键开始安装..." 
        ;;
    *)  
        echo "即将开始安装程序..."
        sleep 1
        ;;
esac

# 1. 清理旧文件（如果存在）
echo "[*]正在清理旧文件..."
rm -f "$TEMP_ZIP"
rm -rf "$TARGET_DIR"

# 2. 下载仓库压缩包
echo "[*]正在下载 PocketMine 安装包..."
curl -L -o "$TEMP_ZIP" "$REPO_URL"

# 检查下载是否成功
if [ ! -f "$TEMP_ZIP" ]; then
    echo "[!]错误：下载失败！请检查网络连接。"
    echo "[*]请尝试科学上网"
    exit 1
fi

# 3. 解压压缩包
echo "[*]正在解压文件..."
unzip -q -o "$TEMP_ZIP" -d "$HOME"

# 检查解压是否成功
if [ ! -d "$HOME/$TEMP_DIR" ]; then
    echo "[?]未知原因解压失败"
    echo "[*]尝试重新启动程序"
    exit 1
fi

# 4. 重命名文件夹
echo "[*]正在设置 PocketMine 目录..."
mv "$HOME/$TEMP_DIR" "$TARGET_DIR"

# 5. 处理插件安装
echo "[*]正在检查插件目录..."
if [ -d "$TARGET_DIR/plugins" ]; then
    read -p "[*]检测到仓库中包含插件目录，是否保留并安装这些插件？(y/N) " plugin_choice
else
    read -p "[*]仓库中未发现插件目录，是否需要手动安装？(y/N) " plugin_choice
fi

case "$plugin_choice" in
    [Yy]*)
        echo "[*]保留插件目录，开始安装..."
        # 插件已存在，无需操作
        ;;
    [Nn]*)
        echo "[*]插件安装已取消，删除插件目录..."
        rm -rf "$TARGET_DIR/plugins"
        ;;
    *)
        echo "[*]无效输入，插件目录已删除..."
        rm -rf "$TARGET_DIR/plugins"
        ;;
esac

# 6. 添加执行权限
echo "[*]设置执行权限..."
chmod +x "$START_SCRIPT"
chmod +x "$PHP_BIN"

# 7. 清理临时文件
echo "[*]清理临时文件..."
rm -f "$TEMP_ZIP"

# 8. 启动 PocketMine
echo "[*]启动 PocketMine 服务器..."
sleep 0.5
echo "[!]您已进入PocketMine安装向导环节，正常操作即可"
cd "$TARGET_DIR"
"$START_SCRIPT"

echo "感谢您的使用"