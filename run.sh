#!/bin/bash

# ============================================================
# Ubuntu Developer Setup Script
# Interactive installation with component selection
# ============================================================

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m'

# Installation options (all enabled by default)
declare -A COMPONENTS=(
    ["system"]=1
    ["php"]=1
    ["nodejs"]=1
    ["database"]=1
    ["nginx"]=1
    ["devtools"]=1
    ["projects"]=1
)

COMPONENT_KEYS=("system" "php" "nodejs" "database" "nginx" "devtools" "projects")
COMPONENT_LABELS=(
    "Sistem Paketleri (git, curl, acl, supervisor)"
    "PHP 8.4 + Composer + Extensions"
    "Node.js 20 + NPM"
    "PostgreSQL + Redis"
    "Nginx + Valet Linux"
    "VS Code + DBeaver"
    "Proje Kurulumları (clone, migrate, horizon)"
)

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Functions
print_header() {
    echo -e "\n${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
    echo -e "${BLUE}  $1${NC}"
    echo -e "${BLUE}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}\n"
}

print_success() { echo -e "${GREEN}✓ $1${NC}"; }
print_warning() { echo -e "${YELLOW}⚠ $1${NC}"; }
print_error() { echo -e "${RED}✗ $1${NC}"; }
print_info() { echo -e "${BLUE}ℹ $1${NC}"; }

toggle_component() {
    local key=$1
    if [ "${COMPONENTS[$key]}" -eq 1 ]; then
        COMPONENTS[$key]=0
    else
        COMPONENTS[$key]=1
    fi
}

select_all() {
    for key in "${COMPONENT_KEYS[@]}"; do
        COMPONENTS[$key]=1
    done
}

select_none() {
    for key in "${COMPONENT_KEYS[@]}"; do
        COMPONENTS[$key]=0
    done
}

get_selected_tags() {
    local tags=""
    for key in "${COMPONENT_KEYS[@]}"; do
        if [ "${COMPONENTS[$key]}" -eq 1 ] && [ "$key" != "projects" ]; then
            if [ -n "$tags" ]; then
                tags="$tags,$key"
            else
                tags="$key"
            fi
        fi
    done
    echo "$tags"
}

display_menu() {
    clear
    print_header "Ubuntu Developer Setup - Kurulum Seçenekleri"
    
    echo -e "Kurmak istediğiniz bileşenleri seçin (numara ile toggle):\n"
    
    for i in "${!COMPONENT_KEYS[@]}"; do
        local key="${COMPONENT_KEYS[$i]}"
        local label="${COMPONENT_LABELS[$i]}"
        local num=$((i + 1))
        
        if [ "${COMPONENTS[$key]}" -eq 1 ]; then
            echo -e "  ${GREEN}[$num] ✓ $label${NC}"
        else
            echo -e "  ${RED}[$num] ✗ $label${NC}"
        fi
    done
    
    echo ""
    echo -e "  ${CYAN}[a] Tümünü Seç${NC}"
    echo -e "  ${CYAN}[n] Tümünü Kaldır${NC}"
    echo -e "  ${CYAN}[s] Kurulumu Başlat${NC}"
    echo -e "  ${CYAN}[q] Çıkış${NC}"
    echo ""
}

run_installation() {
    print_header "Kurulum Başlıyor"
    
    # Install dependencies
    print_info "Bağımlılıklar yükleniyor..."
    sudo apt update
    sudo apt install -y ansible git curl acl software-properties-common
    
    # Get selected software tags
    local tags=$(get_selected_tags)
    
    # Run software installation if any software selected
    if [ -n "$tags" ]; then
        print_header "Yazılım Kurulumu (software.yml)"
        print_info "Seçilen: $tags"
        sudo ansible-playbook "$SCRIPT_DIR/software.yml" --tags "$tags"
    fi
    
    # Run project setup if selected
    if [ "${COMPONENTS["projects"]}" -eq 1 ]; then
        print_header "Proje Kurulumu (projects.yml)"
        sudo ansible-playbook "$SCRIPT_DIR/projects.yml"
    fi
    
    print_header "Kurulum Tamamlandı! 🎉"
    echo -e "Sonraki adımlar:"
    echo -e "  1. Oturumu kapatıp açın: ${YELLOW}source ~/.bashrc${NC}"
    echo -e "  2. Valet kontrolü: ${YELLOW}valet status${NC}"
    echo -e "  3. Horizon kontrolü: ${YELLOW}sudo supervisorctl status${NC}"
}

# Check for --all flag (skip menu)
if [ "$1" == "--all" ] || [ "$1" == "-a" ]; then
    select_all
    run_installation
    exit 0
fi

# Interactive menu
while true; do
    display_menu
    read -p "Seçiminiz: " choice
    
    case $choice in
        1) toggle_component "system" ;;
        2) toggle_component "php" ;;
        3) toggle_component "nodejs" ;;
        4) toggle_component "database" ;;
        5) toggle_component "nginx" ;;
        6) toggle_component "devtools" ;;
        7) toggle_component "projects" ;;
        a|A) select_all ;;
        n|N) select_none ;;
        s|S) run_installation; exit 0 ;;
        q|Q) echo "Çıkış yapılıyor..."; exit 0 ;;
        *) print_error "Geçersiz seçim" ;;
    esac
done
