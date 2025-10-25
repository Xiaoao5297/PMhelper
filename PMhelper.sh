#!/bin/bash

# PMHelper - 可扩展版本
# v2.1
# 作者：Xiaoao

# 定义常量
GITHUB="https://github.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/raw/main"
PMMP_DIR="$HOME/PocketMine/"
START_PMMP="$PMMP_DIR/start.sh"
BACKTITLE="PocketMine - PMHelper v2.1"
PMMP_GITHUB="https://github.com/pmmp/PocketMine-MP/releases/download"
# 颜色定义
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color
TITLE_COLOR="\Z0\Zb"

# ================================
# 基础函数定义
# ================================

# 日志函数
log() {
    echo -e "${GREEN}[INFO]${NC} $(date '+%Y-%m-%d %H:%M:%S') - $1"
}

warn() {
    echo -e "${YELLOW}[WARN]${NC} $(date '+%Y-%m-%d %H:%M:%S') - $1"
}

error() {
    echo -e "${RED}[ERROR]${NC} $(date '+%Y-%m-%d %H:%M:%S') - $1"
}

# 对话框函数
show_msg() {
    dialog --backtitle "$BACKTITLE" \
           --colors \
           --title "${TITLE_COLOR}$1\Zn" \
           --msgbox "$2" 0 0
}

show_yesno() {
    dialog --backtitle "$BACKTITLE" \
           --colors \
           --title "${TITLE_COLOR}$1\Zn" \
           --yesno "$2" 0 0
    return $?
}

show_menu() {
    local title="$1" prompt="$2"
    shift 2
    
    local menu_items=()
    while [ $# -gt 0 ]; do
        menu_items+=("$1" "$2")
        shift 2
    done
    
    dialog --backtitle "$BACKTITLE" \
           --colors \
           --title "${TITLE_COLOR}$title\Zn" \
           --menu "$prompt" 0 0 0 \
           "${menu_items[@]}" \
           3>&1 1>&2 2>&3
}

# 下载函数
download_file() {
    local url="$1" dest="$2"
    log "下载: $url"
    if wget -q --show-progress -O "$dest" "$url"; then
        log "下载成功: $(basename "$dest")"
        return 0
    else
        error "下载失败: $url"
        return 1
    fi
}

# 检查依赖
check_dependencies() {
    local deps=("wget" "curl")
    for dep in "${deps[@]}"; do
        if ! command -v "$dep" &> /dev/null; then
            log "安装依赖: $dep"
            pkg install "$dep" -y
        fi
    done
    
    # 检查dialog
    if ! command -v dialog &> /dev/null; then
        log "安装dialog..."
        pkg install dialog -y
    fi
}

# ================================
# 版本配置数据库 - 在这里添加新版本！
# ================================

declare -A VERSION_DATABASE=(
    # 格式: [版本代码]="显示名称|PHP二进制URL|核心文件URL|PHP版本|分类|推荐级别"
    # 0.11.x 系列  
    ["01101"]="和谐核心 更新版|${GITHUB}/bins/php562|${GITHUB}/srcs/0.11/乌兰托娅万岁改造和谐核心_更新.phar|5.6|0.11.x"
    ["01102"]="流星核心|${GITHUB}/bins/php562|${GITHUB}/srcs/0.11/乌兰托娅万岁改造流星核心.phar|5.6|0.11.x"
    
    # 0.13.x系列
    ["01301"]="Genisys v0.13.x|${GITHUB}/bins/php562|${GITHUB}/srcs/0.13/"
    
    # 0.14.x 系列
    ["01401"]="Genisys GrassMC v0.14.x|${GITHUB}/bins/php|${GITHUB}/srcs/0.14/Genisys_GrassMC_v0.14.x.phar|7.2|0.14.x"
    ["01402"]="Genisys v0.14.x|${GITHUB}/bins/php704|${GITHUB}/srcs/0.14/Genisys_v0.14.x.phar|7.0|0.14.x"
    ["01403"]="Genisys 1.1dev|${GITHUB}/bins/php704|${GITHUB}/srcs/0.14/Genisys_1.1dev.phar|7.0|0.14.x"
    ["01404"]="ClearSky v0.14.x|${GITHUB}/bins/php562|${GITHUB}/srcs/0.14/ClearSky_v0.14.x.phar|5.6|0.14.x"
    ["01405"]="Genisys php5|${GITHUB}/bins/php562|${GITHUB}/srcs/0.14/Genisys_php5.phar|5.6|0.14.x"
    ["01406"]="ITX php5|${GITHUB}/bins/php562|${GITHUB}/srcs/0.14/ITXPHP5.phar|5.6|0.14.x"
    
    # 0.15.x 系列
    ["01501"]="Genisys v0.15.x|${GITHUB}/bins/php704|${GITHUB}/srcs/0.15/Genisys_v0.15.x.phar|7.0|0.15.x"
        
    # 1.2.x 系列 - 新增示例
    ["12001"]="PocketMine-MP 1.2.12|${GITHUB}/bins/php|${GITHUB}/srcs/1.2.12/PocketMine-MP1.2.12.phar|7.2|1.2.x"
    
    ["12101"]="PocketMine-MP|${GITHUB}/bins/php8/php82|${PMMP_GITHUB}/5.36.0/PocketMine-MP.phar|8.2|1.21.111"
)

# 版本分类显示配置
declare -A VERSION_CATEGORIES=(
    ["011"]="0.11.x"
    ["013"]="0.13.x"
    ["014"]="0.14.x" 
    ["015"]="0.15.x"
    ["016"]="0.16.x"
    ["102"]="1.2.x"
    ["120"]="1.20.x"
    ["121"]="1.21.x"
)

# ================================
# 核心功能函数
# ================================

# 获取分类菜单
show_category_menu() {
    local menu_items=("00" "返回主菜单")
    
    # 按分类代码排序
    for category_code in $(echo "${!VERSION_CATEGORIES[@]}" | tr ' ' '\n' | sort); do
        menu_items+=("$category_code" "${VERSION_CATEGORIES[$category_code]}")
    done
    
    show_menu "选择版本分类" "请选择要安装的版本分类" "${menu_items[@]}"
}

# 显示版本选择菜单
# 显示版本选择菜单
show_version_menu() {
    local category="$1"
    local category_name="${VERSION_CATEGORIES[$category]}"
    
    local menu_items=("00" "返回上一步")
    
    # 获取该分类下的所有版本
    for version_code in "${!VERSION_DATABASE[@]}"; do
        # 使用精确的前缀匹配
        if [[ "$version_code" =~ ^"$category" ]]; then
            local info="${VERSION_DATABASE[$version_code]}"
            local name=$(echo "$info" | cut -d'|' -f1)
            local php_ver=$(echo "$info" | cut -d'|' -f4)
            
            menu_items+=("$version_code" "$name (PHP$php_ver)")
        fi
    done
    
    # 如果没有找到版本，显示提示
    if [ ${#menu_items[@]} -eq 2 ]; then
        show_msg "提示" "分类 $category_name 下没有找到可用的版本"
        return
    fi
    
    show_menu "选择版本 - $category_name" "请选择具体版本" "${menu_items[@]}"
}

# 获取版本信息
get_version_info() {
    local version_code="$1"
    local info="${VERSION_DATABASE[$version_code]}"
    
    if [[ -n "$info" ]]; then
        echo "$info"
    else
        return 1
    fi
}

# 安装选定的版本
install_selected_version() {
    local version_code="$1"
    
    local info=$(get_version_info "$version_code")
    if [[ $? -ne 0 ]]; then
        error "无效的版本代码: $version_code"
        return 1
    fi
    
    local name=$(echo "$info" | cut -d'|' -f1)
    local php_url=$(echo "$info" | cut -d'|' -f2)
    local core_url=$(echo "$info" | cut -d'|' -f3)
    local php_ver=$(echo "$info" | cut -d'|' -f4)
    local category=$(echo "$info" | cut -d'|' -f5)
    #local recommend=$(echo "$info" | cut -d'|' -f6)
    
    log "开始安装: $name"
    log "版本: $category | PHP: $php_ver"
    
    # 检查现有安装
    if [[ -d "$PMMP_DIR" ]]; then
        show_yesno "警告" "检测到已存在的PocketMine安装。继续安装将删除所有数据！如果没有重要文件可以放心下一步"
        [[ $? -ne 0 ]] && return 1
        rm -rf "$PMMP_DIR"
    fi
    mkdir -p "$PMMP_DIR"
    # 创建目录
    mkdir -p "$PMMP_DIR/bin"
    
    # 下载文件
    log "下载必要文件..."
    
    if ! download_file "$php_url" "$PMMP_DIR/bin/php"; then
        error "PHP二进制下载失败"
        return 1
    fi
    
    if ! download_file "$core_url" "$PMMP_DIR/PocketMine-MP.phar"; then
        error "核心文件下载失败"
        return 1
    fi
    
    if ! download_file "$GITHUB/start.sh" "$START_PMMP"; then
        error "启动脚本下载失败"
        return 1
    fi
    
    if ! download_file "$GITHUB/php.ini" "$PMMP_DIR/php.ini"; then
        warn "php.ini下载失败，将使用默认配置"
    fi
    
    # 设置权限
    chmod -R +x "$PMMP_DIR"
    chmod +x "$PMMP_DIR/bin/php" "$START_PMMP"
    
    log "安装完成！"
    return 0
}

# 启动服务器
start_server() {
    if [[ ! -d "$PMMP_DIR" ]]; then
        show_msg "错误" "PocketMine目录不存在，请先安装"
        return 1
    fi

    if [[ ! -f "$START_PMMP" ]]; then
        show_msg "错误" "启动脚本不存在"
        return 1
    fi

    show_yesno "确认" "是否启动PocketMine服务器？"
    [[ $? -eq 0 ]] && {
        clear
        log "启动服务器..."
        "$START_PMMP"
    }
}

# 别名管理
manage_alias() {
    local alias_name="$1" alias_command="$2" shell_type="$3"
    
    case "$shell_type" in
        "bash")
            local bashrc="$HOME/.bashrc"
            if [ -f "$bashrc" ]; then
                if grep -q "alias $alias_name=" "$bashrc"; then
                    sed -i "/alias $alias_name=/d" "$bashrc"
                fi
                echo "alias $alias_name='$alias_command'" >> "$bashrc"
                log "Bash别名 $alias_name 已设置"
            fi
            ;;
        "fish")
            local fish_config="$HOME/.config/fish/config.fish"
            if [ -f "$fish_config" ]; then
                if grep -q "alias $alias_name=" "$fish_config"; then
                    sed -i "/alias $alias_name=/d" "$fish_config"
                fi
                echo "alias $alias_name='$alias_command'" >> "$fish_config"
                log "Fish别名 $alias_name 已设置"
            fi
            ;;
    esac
}

setup_aliases() {
    log "设置命令别名..."
    
    # 设置服务器启动别名
    manage_alias "mc" "bash $START_PMMP" "bash"
    
    # 设置PMHelper启动别名
    local pmh_command='bash -c "$(curl -L https://raw.githubusercontent.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/main/PMhelper.sh)"'
    manage_alias "pmh" "$pmh_command" "bash"
    
    # Fish配置
    if [ -d "$HOME/.config/fish" ]; then
        manage_alias "mc" "bash $START_PMMP" "fish"
        manage_alias "pmh" "$pmh_command" "fish"
    fi
    
    show_msg "成功" "别名设置完成！\n\n使用说明：\n- 输入 'mc' 启动服务器\n- 输入 'pmh' 启动PMHelper\n\n重启终端后生效"
}

# ================================
# 安装流程主函数
# ================================

install_pocketmine() {
    while true; do
        # 选择分类
        local category=$(show_category_menu)
        [[ -z "$category" ]] && return 1
        [[ "$category" == "00" ]] && return 1
        
        # 选择具体版本
        local version_code=$(show_version_menu "$category")
        [[ -z "$version_code" ]] && continue
        [[ "$version_code" == "00" ]] && continue
        
        # 安装版本
        if install_selected_version "$version_code"; then
            return 0
        else
            show_yesno "错误" "安装失败，是否重新选择版本？"
            [[ $? -ne 0 ]] && return 1
        fi
    done
}

# ================================
# 设置菜单
# ================================

settings_menu() {
    local choice=$(show_menu "设置" "请选择设置选项" \
        "1" "设置服务器快捷启动 (mc)" \
        "2" "设置PMHelper快捷启动 (pmh)" \
        "3" "设置所有别名" \
        "0" "返回")
    
    case "$choice" in
        "1")
            manage_alias "mc" "bash $START_PMMP" "bash"
            [[ -d "$HOME/.config/fish" ]] && manage_alias "mc" "bash $START_PMMP" "fish"
            show_msg "成功" "服务器启动别名 'mc' 已设置"
            ;;
        "2") 
            local pmh_command='bash -c "$(curl -L https://raw.githubusercontent.com/Xiaoao5297/Termux-PocketMine0.14.x-Auto-Installer/main/PMhelper.sh)"'
            manage_alias "pmh" "$pmh_command" "bash"
            [[ -d "$HOME/.config/fish" ]] && manage_alias "pmh" "$pmh_command" "fish"
            show_msg "成功" "PMHelper别名 'pmh' 已设置"
            ;;
        "3")
            setup_aliases
            ;;
        "0") return ;;
    esac
}

# ================================
# 版本管理功能
# ================================

# 显示版本数据库
list_version_database() {
    clear
    echo "当前版本数据库:"
    echo "========================"
    
    for category_code in $(echo "${!VERSION_CATEGORIES[@]}" | tr ' ' '\n' | sort); do
        echo "分类: ${VERSION_CATEGORIES[$category_code]}"
        echo "------------------------"
        
        for version_code in $(echo "${!VERSION_DATABASE[@]}" | tr ' ' '\n' | sort); do
            if [[ "$version_code" == "$category_code"* ]]; then
                local info="${VERSION_DATABASE[$version_code]}"
                local name=$(echo "$info" | cut -d'|' -f1)
                local php_ver=$(echo "$info" | cut -d'|' -f4)
                local recommend=$(echo "$info" | cut -d'|' -f6)
                echo "  $version_code: $name (PHP$php_ver) - $recommend"
            fi
        done
        echo
    done
    
    read -p "按回车键继续..."
}

# 添加新版本的函数
add_new_version() {
    clear
    echo "添加新版本到数据库:"
    echo "========================"
    
    read -p "版本代码 (6位数字，如01401): " code
    read -p "显示名称: " name
    read -p "PHP二进制URL: " php_url
    read -p "核心文件URL: " core_url
    read -p "PHP版本: " php_ver
    read -p "分类代码 (3位，如014): " category
    read -p "推荐级别: " recommend
    
    echo
    echo "请将以下行添加到 VERSION_DATABASE 数组中:"
    echo "[\"$code\"]=\"$name|$php_url|$core_url|$php_ver|$category|$recommend\""
    echo
    echo "如果分类不存在，请同时添加到 VERSION_CATEGORIES:"
    read -p "分类显示名称: " category_name
    echo "[\"$category\"]=\"$category_name\""
    echo
    read -p "按回车键继续..."
}

# 开发者菜单
developer_menu() {
    local choice=$(show_menu "开发者工具" "版本管理工具" \
        "1" "列出所有版本" \
        "2" "添加新版本" \
        "0" "返回")
    
    case "$choice" in
        "1") 
            list_version_database
            ;;
        "2") 
            add_new_version
            ;;
        "0") return ;;
    esac
}

# ================================
# 主菜单
# ================================

main_menu() {
    while true; do
        local choice=$(show_menu "PMHelper v2.1" "PocketMine服务器管理工具" \
            "1" "安装PocketMine" \
            "2" "启动服务器" \
            "3" "服务器设置" \
            "4" "查看版本库" \
            "9" "开发者工具" \
            "0" "退出")
        
        case "$choice" in
            "1") 
                if install_pocketmine; then
                    show_yesno "安装完成" "是否现在启动服务器？" && start_server
                    show_yesno "别名设置" "是否设置命令别名？" && setup_aliases
                fi
                ;;
            "2") 
                start_server
                ;;
            "3") 
                settings_menu
                ;;
            "4") 
                list_version_database
                ;;
            "9") 
                developer_menu
                ;;
            "0") 
                show_yesno "确认" "确定要退出吗？" 
                [[ $? -eq 0 ]] && clear && break
                ;;
        esac
    done
}

# ================================
# 初始化
# ================================

init() {
    clear
    log "PMHelper v2.1 启动"
    check_dependencies
    
    # 创建必要目录
    # mkdir -p "$HOME/PocketMine"
    
    # 清理临时文件
    #[[ -d "/tmp/PMHelper" ]] && rm -rf "/tmp/PMHelper"
}

# ================================
# 主程序
# ================================

main() {
    init
    trap 'clear; exit 0' INT TERM
    main_menu
}

# 启动脚本
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
fi
