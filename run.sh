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
    ["docker"]=1
    ["nodejs"]=1
    ["devtools"]=1
    ["antigravity"]=1
    ["projects"]=1
)

COMPONENT_KEYS=("system" "docker" "nodejs" "devtools" "antigravity" "projects")
COMPONENT_LABELS=(
    "System Packages (git, curl, acl, supervisor)"
    "Docker + Docker Compose (for Laravel Sail)"
    "Node.js 20 + NPM"
    "VS Code + DBeaver"
    "Google Antigravity Editor"
    "Project Setup (Sail, containers, migrate)"
)

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Target user for installation (always current user)
TARGET_USER="${SUDO_USER:-$USER}"

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
    print_header "Ubuntu Developer Setup - Installation Options"
    
    echo -e "${CYAN}Installation User: ${GREEN}$TARGET_USER${NC}\n"
    
    echo -e "Select components to install (toggle with number):\n"
    
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
    echo -e "  ${CYAN}[a] Select All${NC}"
    echo -e "  ${CYAN}[n] Select None${NC}"
    echo -e "  ${CYAN}[s] Start Installation${NC}"
    echo -e "  ${CYAN}[q] Quit${NC}"
    echo ""
}

run_installation() {
    print_header "Starting Installation"
    
    print_info "Target User: $TARGET_USER"
    
    # Install dependencies
    print_info "Installing dependencies..."
    sudo apt update
    sudo apt install -y ansible git curl acl software-properties-common
    
    # Get selected software tags
    local tags=$(get_selected_tags)
    
    # Run software installation if any software selected
    if [ -n "$tags" ]; then
        print_header "Software Installation (software.yml)"
        print_info "Selected: $tags"
        print_info "Installing for user: $TARGET_USER"
        sudo ansible-playbook "$SCRIPT_DIR/software.yml" --tags "$tags" --extra-vars "target_user=$TARGET_USER"
    fi
    
    # Run project setup if selected
    if [ "${COMPONENTS["projects"]}" -eq 1 ]; then
        print_header "Project Setup (projects.yml)"
        print_info "Setting up projects for user: $TARGET_USER"
        sudo ansible-playbook "$SCRIPT_DIR/projects.yml" --extra-vars "target_user=$TARGET_USER"
    fi
    
    print_header "Installation Complete! 🎉"
    echo -e "Installation user: ${GREEN}$TARGET_USER${NC}"
    echo -e "Next steps:"
    echo -e "  1. Log out and back in to apply Docker group: ${YELLOW}newgrp docker${NC}"
    echo -e "  2. Navigate to project: ${YELLOW}cd /var/www/projects/zone${NC}"
    echo -e "  3. Start Sail containers: ${YELLOW}./vendor/bin/sail up -d${NC}"
    echo -e "  4. Check containers: ${YELLOW}docker ps${NC}"
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
    read -p "Your choice: " choice
    
    case $choice in
        1) toggle_component "system" ;;
        2) toggle_component "docker" ;;
        3) toggle_component "nodejs" ;;
        4) toggle_component "devtools" ;;
        5) toggle_component "antigravity" ;;
        6) toggle_component "projects" ;;
        a|A) select_all ;;
        n|N) select_none ;;
        s|S) run_installation; exit 0 ;;
        q|Q) echo "Exiting..."; exit 0 ;;
        *) print_error "Invalid choice" ;;
    esac
done
