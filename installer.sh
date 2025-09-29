#!/bin/bash

# PocketMine 自动安装脚本
# 作者：你的名字
# 描述：自动下载并安装 PocketMine 服务器

# 定义变量
REPO_URL="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/archive/refs/heads/main.zip"
TEMP_ZIP="pocketmine_temp.zip"
TEMP_DIR="Termux-PocketMine0.14.x-Auto-Installer-main"
TARGET_DIR="$HOME/PocketMine"
START_SCRIPT="$TARGET_DIR/start.sh"
PHP_BIN="$TARGET_DIR/bin/php"

# 1. 清理旧文件（如果存在）
echo "正在清理旧文件..."
rm -f "$TEMP_ZIP"
rm -rf "$TARGET_DIR"

# 2. 下载仓库压缩包
echo "正在下载 PocketMine 安装包..."
curl -L -o "$TEMP_ZIP" "$REPO_URL"

# 检查下载是否成功
if [ ! -f "$TEMP_ZIP" ]; then
    echo "错误：下载失败！请检查网络连接。"
    exit 1
fi

# 3. 解压压缩包
echo "正在解压文件..."
unzip -q "$TEMP_ZIP" -d "$HOME"

# 检查解压是否成功
if [ ! -d "$HOME/$TEMP_DIR" ]; then
    echo "错误：解压失败！"
    exit 1
fi

# 4. 重命名文件夹
echo "正在设置 PocketMine 目录..."
mv "$HOME/$TEMP_DIR" "$TARGET_DIR"

# 5. 添加执行权限
echo "设置执行权限..."
chmod +x "$START_SCRIPT"
chmod +x "$PHP_BIN"

# 6. 清理临时文件
echo "清理临时文件..."
rm -f "$TEMP_ZIP"

# 7. 启动 PocketMine
echo "启动 PocketMine 服务器..."
cd "$TARGET_DIR"
"$START_SCRIPT"

echo "PocketMine 服务器已启动！"
