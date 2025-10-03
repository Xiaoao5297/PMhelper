#!/bin/bash

# PMHelper
# v1.0
# 作者：Xiaoao

# -ne no
# -eq yes

#定义变量
pmmp_dir="$HOME/PocketMine/"
PMMP_STARTSH="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/raw/refs/heads/main/start.sh"
PMMP_PHPINI="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/raw/refs/heads/main/php.ini"
PMMP_DIR="$HOME/PocketMine/"
START_PMMP="$HOME/PocketMine/start.sh"
#PHP_5=""
#PHP_70=""
#PHP_72=""
#PHP_73=""
# 初始化dialog
BACKTITLE="PocketMine - PMHelper v1.0"

# 检测是否有 dialog
#if ! command -v dialog &> /dev/null; then
#    echo "未安装 dialog，将自动安装..."
#    pkg install dialog -y
#fi

# 显示消息对话框函数
show_msg() {
    dialog --backtitle "$BACKTITLE" --title "$1" --msgbox "$2" 0 0
}

# 显示确认对话框函数
show_yesno() {
    dialog --backtitle "$BACKTITLE" --title "$1" --yesno "$2" 0 0
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

# 显示菜单函数
show_menu() {
    local title="$1"
    local prompt="$2"
    shift 2
    
    # 构建菜单选项
    local menu_items=()
    while [ $# -gt 0 ]; do
        menu_items+=("$1" "$2")
        shift 2
    done
    
    # 使用 dialog 创建菜单
    choice=$(dialog --backtitle "$BACKTITLE" \
                   --title "$title" \
                   --menu "$prompt" 0 0 0 \
                   "${menu_items[@]}" \
                   3>&1 1>&2 2>&3)
    
    echo "$choice"
}

# 选项一函数
main_1() {
    if [[ -d "$HOME/Pocketmine/" ]] && [[ -d "$PM_DIR/src" || -f "$PM_DIR/PocketMine-MP.phar" ]]; then
        #echo "条件成立：PocketMine 目录存在，并且包含 src 或 PocketMine-MP.phar"
        show_yesno "提示" "检测到你已经安装了 PocketMine 是否重装？重装会失去所有数据！\n如果不想重装，请输入Ctrl+Z退出"
        if [ $? -eq 0 ]; then
            rm -rf "$HOME/PocketMine/"
        fi
    else
        #echo "条件不成立：PocketMine 目录不存在，或者缺少 src 和 PocketMine-MP.phar"
        if [[ -d "$HOME/PocketMine/" ]];then
            show_yesno "提示" "检测到${HOME}/PocketMine/目录下没有 src 或者 PocketMine-MP.phar，是否重装？\n注意，请尽快检查此目录是否有重要文件！\n如果不想重装，请输入Ctrl+Z退出"
            if [ -$? -eq 0 ]; then
                rm -rf "$HOME/PocketMine/"
            fi
        #else
            #show_msg "安装" "回车键开始安装..."
        fi
    fi
    
    #选择大版本菜单
    choise_main_1=$(show_menu "选择版本" "请选择一个版本来安装..."\
        "00" "返回主菜单"\
        "01" "####主流版本####"\
        "02" "0.14.x"\
        "03" "0.15.x"\
        "04" "1.1.x"\
        "05" "####其他版本####"\
        "06" "0.11.x"\
        "07" "0.13.x"\
        "08" "0.16.x"\
        "09" "1.0.x"\
        "10" "1.2.12")
        
    
    case "$choise_main_1" in
        00 | 01 | 05)
            main_menu
            ;;
        02)
            #0.14内核选择菜单
            choice_PMMP_src=$(show_menu "选择版本 - 0.14.x" "选择一个核心...推荐选择第1个"\
            "00" "返回主菜单"\
            "01" "Genisys_GrassMC_v0.14.x.phar - php7.2"\
            "02" "Genisys_v0.14.x.phar - php7.0"\
            "03" "Genisys_1.1dev.phar - php7.0"\
            "04" "ClearSky_v0.14.x.phar - php5"\
            "05" "ITXPHP5.phar - php5"\
            "06" "Genisys_php5.phar - php5"
            )
            
            #0.14检测不同内核的php选择
            case $choice_PMMP_src in
                00)
                    main_menu
                    ;;
                01)
                    php="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/raw/refs/heads/main/bins/php" #php7.2
                    #我也不知道为什么地址这么长...
                    ;;
                02 | 03)
                    php="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/raw/refs/heads/main/bins/php704" #php7.0
                    ;;
                04 | 05 | 06)
                    php="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/raw/refs/heads/main/bins/php562" #php5
                    ;;
            esac
            #0.14选择版本
            #不想写屎山代码啊啊啊啊
            case $choice_PMMP_src in
                01)
                    php_src="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/raw/refs/heads/main/srcs/0.14/Genisys_GrassMC_v0.14.x.phar"
                    ;;
                02)
                    php_src="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/raw/refs/heads/main/srcs/0.14/Genisys_v0.14.x.phar"
                    ;;
                03)
                    php_src="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/raw/refs/heads/main/srcs/0.14/Genisys_1.1dev.phar"
                    ;;
                04)
                    php_src="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/raw/refs/heads/main/srcs/0.14/ClearSky_v0.14.x.phar"
                    ;;
                05)
                    php_src="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/raw/refs/heads/main/srcs/0.14/ITXPHP5.phar"
                    ;;
                06)
                    php_src="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/raw/refs/heads/main/srcs/0.14/Genisys_php5.phar"
                    ;;
            esac
            ;;
            
        03)
            #0.15内核选择菜单
            choice_PMMP_src=$(show_menu "选择版本 - 0.15.x" "选择一个核心来安装...推荐使用第一个"\
            "00" "返回主菜单"\
            "01" "Genisys_v0.15.x.phar php7.0"
            )
            
            case $choice_PMMP_src in
                00)
                    main_menu
                    ;;
                01)
                    php="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/raw/refs/heads/main/bins/php704"
                    ;;
            esac
            case $choice_PMMP_src in
                01)
                    php_src="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/raw/refs/heads/main/srcs/0.15/Genisys_v0.15.x.phar"
                    ;;
            esac
            ;;
        04)
            #1.1内核选择菜单
            choice_PMMP_src=$(show_menu "选择版本 - 1.1.x" "选择一个核心来安装...推荐使用第一个"\
            "00" "返回主菜单"\
            "01" "GenisysPro_v1.1.x.phar php7.2"
            )
            
            case $choice_PMMP_src in
                00)
                    main_menu
                    ;;
                01)
                    php="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/raw/refs/heads/main/bins/php"
                    ;;
            esac
            case $choice_PMMP_src in
                01)
                    php_src="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/raw/refs/heads/main/srcs/1.1/GenisysPro_v1.1.x.phar"
                    ;;
            esac
            ;;
        06)
            choice_PMMP_src=$(show_menu "选择版本 - 0.11.x" "选择一个内核来安装...推荐使用第一个"\
            "00" "返回主菜单"\
            "01" "乌兰托娅万岁改造和谐核心_更新.phar php5"\
            "02" "乌兰托娅万岁改造流星核心.phar php5"
            )
            
            case $choice_PMMP_src in
                00)
                    main_menu
                    ;;
                01 | 02)
                    php="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/raw/refs/heads/main/bins/php562"
                    ;;
            esac
            case $choice_PMMP_src in
                01)
                    php_src="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/raw/refs/heads/main/srcs/0.11/乌兰托娅万岁改造和谐核心_更新.phar"
                    ;;
                02)
                    php_src="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/raw/refs/heads/main/srcs/0.11/乌兰托娅万岁改造流星核心.phar"
                    ;;
            esac
            ;;
        07)
            choise_PMMP_src=$(show_menu "选择版本 - 0.13.x" "选择一个内核来安装...推荐使用第一个"\
            "00" "返回主菜单"\
            "01" "PocketMine-MP_1.7WTB.phar php5"\
            "02" "乌兰托娅0.13.phar(有bug) php5"\
            "03" "Genisys.phar 0.13.1 php7"
            )
            case $choise_PMMP_src in
                00)
                    main_menu
                    ;;
                01 | 02)
                    php="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/raw/refs/heads/main/bins/php562"
                    ;;
                03)
                    php="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/raw/refs/heads/main/bins/php704"
                    ;;
            esac
            case $choise_PMMP_src in
                01)
                    php_src="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/raw/refs/heads/main/srcs/0.13/PocketMine-MP_1.7WTB.phar"
                    ;;
                02)
                    php_src="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/raw/refs/heads/main/srcs/0.13/乌兰托娅0.13.phar"
                    ;;
                03)
                    php_src="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/raw/refs/heads/main/srcs/0.13/Genisys.phar"
                    ;;
            esac
            ;;
        08)
            choice_PMMP_src=$(show_menu "选择版本 - 0.16.x" "选择一个内核来安装...推荐使用第一个"\
            "00" "返回主菜单"\
            "01" "Genisys_v0.16.x.phar php7.0"
            )
            case $choice_PMMP_src in
                00)
                    main_menu
                    ;;
                01)
                    php="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/raw/refs/heads/main/bins/php704"
                    ;;
            esac
            case $choice_PMMP_src in
                01)
                    php_src="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/raw/refs/heads/main/srcs/0.16/Genisys_v0.16.x.phar"
                    ;;
            esac
            ;;
        09)
            choice_PMMP_src=$(show_menu "选择版本 - 1.0.x" "选择一个内核来安装...推荐使用第一个"\
            "00" "返回主菜单"\
            "01" "PocketMine-MP1.0.phar php7.0"
            )
            case $choice_PMMP_src in
                00)
                    main_menu
                    ;;
                01)
                    php="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/raw/refs/heads/main/bins/php704"
                    ;;
            esac
            case $choice_PMMP_src in
                01)
                    php_src="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/raw/refs/heads/main/srcs/1.0/PocketMine-MP1.0.phar"
                    ;;
            esac
            ;;
        10)
            choice_PMMP_src=$(show_menu "选择版本 - 1.2.12" "选择一个内核来安装...推荐使用第一个"\
            "00" "返回主菜单"\
            "01" "PocketMine-MP1.2.12.phar php7.2"
            )
            case $choice_PMMP_src in
            00)
                main_menu
                ;;
                01)
                    php="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/raw/refs/heads/main/bins/php"
                    ;;
            esac
            case $choice_PMMP_src in
                01)
                    php_src="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/raw/refs/heads/main/srcs/1.2.12/PocketMine-MP1.2.12.phar"
                    ;;
            esac
            ;;
    esac
    
    clear
    
    echo "========下载清单========"
    echo "下载php文件: $php"
    echo "下载src核心: $php_src"
    echo "下载start.sh: $PMMP_STARTSH"
    echo "下载php.ini: $PMMP_PHPINI"
    #终于做完了史山！！！
    #下载部分
    clear
    echo "正在下载..."
    mkdir "$PMMP_DIR"
    
    wget -P "$PMMP_DIR" "$php"
    wget -P "$PMMP_DIR" "$php_src"
    wget -P "$PMMP_DIR" "$PMMP_STARTSH"
    wget -P "$PMMP_DIR" "$PMMP_PHPINI"
    echo "下载完成"
    mkdir "$HOME/PocketMine/bin/"
    # 移动并重命名所有匹配的 PHP 文件
    #mv -f "$HOME"/PocketMine/php* "$HOME"/PocketMine/bin/php
    #^^^^^^^^^
    #|||||||||为什么这段代码跑不起来???????why?????????
    #不管了，再写一个史山
    #php, php73, php562, php702, php704, php724
    if [ -f "$HOME/PocketMine/php" ]; then
        echo "无需重命名文件名"
    elif [ -f "$HOME/PocketMine/php73" ]; then
        mv "$HOME/PocketMine/php73" "$HOME/PocketMine/php"
        mv "$HOME/PocketMine/php" "$HOME/PocketMine/bin/php"
    elif [ -f "$HOME/PocketMine/php562" ]; then
        mv "$HOME/PocketMine/php562" "$HOME/PocketMine/php"
        mv "$HOME/PocketMine/php" "$HOME/PocketMine/bin/php"
    elif [ -f "$HOME/PocketMine/php702" ]; then
        mv "$HOME/PocketMine/php702" "$HOME/PocketMine/php"
        mv "$HOME/PocketMine/php" "$HOME/PocketMine/bin/php"
        elif [ -f "$HOME/PocketMine/php704" ]; then
        mv "$HOME/PocketMine/php704" "$HOME/PocketMine/php"
        mv "$HOME/PocketMine/php" "$HOME/PocketMine/bin/php"
        elif [ -f "$HOME/PocketMine/php724" ]; then
        mv "$HOME/PocketMine/php724" "$HOME/PocketMine/php"
        mv "$HOME/PocketMine/php" "$HOME/PocketMine/bin/php"
    fi
        
    chmod -R +x "$PMMP_DIR"
    mv "$HOME"/PocketMine/*.phar "$HOME/PocketMine/PocketMine-MP.phar"
    show_msg "提示" "PocketMine 下载完成，是否启动？"

    if [ $? -eq 0 ]; then
        read -p "提示：以后你可以输入mc启动服务器，输入stop停止服务器。如果没问题，就敲下 回车 ，尽情享用吧~"
        "$START_PMMP"
    fi
    
    #read -p "提示：以后你可以输入mc启动服务器，输入stop停止服务器。如果没问题，就敲下 回车 "
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
    
    if [ -f "$HOME/.bashrc" ]; then
        echo "[*]重新加载Bash配置..."
        source "$HOME/.bashrc"
    fi

# 检测并重新加载Fish配置
    if [ -f "$HOME/.config/fish/config.fish" ]; then
        echo "[*]重新加载Fish配置..."
        fish -c 'source ~/.config/fish/config.fish'
    fi
    
    show_msg "提示" "别名已设置，可以通过输入\"mc\"启动服务器，是否退出？"
    if [ $? -eq 0 ]; then
        clear
        exit 0
    fi
}

# 选项二函数
main_2() {
    show_msg "提示" "暂未开发..."
}

# 选项三函数
main_3() {
    show_msg "提示" "暂未开发..."
}

# 主菜单函数
main_menu() {
    while true; do
        # 显示菜单并获取用户选择
        choice=$(show_menu "主菜单" "用↑↓键选择，回车键确定" \
            "1" "安装PocketMine" \
            "2" "施工中...🚧" \
            "3" "施工中...🚧" \
            "0" "退出")
        
        # 处理用户选择
        case "$choice" in
            1)
                main_1
                ;;
            2)
                main_2
                ;;
            3)
                main_3
                ;;
            0)
                #show_msg "退出" "感谢使用！"
                clear
                exit 0
                ;;
            "")
                # 用户取消或按ESC
                show_yesno "确认退出" "您确定要退出吗？"
                if [ $? -eq 0 ]; then
                    #show_msg "退出" "感谢使用！"
                    clear
                    exit 0
                fi
                ;;
        esac
    done
}

# 清理缓存文件
if [ -d "$HOME/PMHelperTMP/" ]; then
    rm -rf "$HOME/PMHelperTMP/"
fi
# 启动主菜单
main_menu
