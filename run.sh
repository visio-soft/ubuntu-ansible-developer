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
    ["antigravity"]=1
    ["projects"]=1
)

COMPONENT_KEYS=("system" "php" "nodejs" "database" "nginx" "devtools" "antigravity" "projects")
COMPONENT_LABELS=(
    "System Packages (git, curl, acl, supervisor)"
    "PHP 8.4 + Composer + Extensions"
    "Node.js 20 + NPM"
    "PostgreSQL + Redis"
    "Nginx"
    "VS Code + DBeaver"
    "Google Antigravity Editor"
    "Project Setup (clone, migrate, horizon)"
)

SCRIPT_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

# Target user for installation (set by user selection)
TARGET_USER=""

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

prompt_for_user() {
    clear
    print_header "User Selection for Installation"
    
    # Get current user (works even with sudo)
    CURRENT_USER="${SUDO_USER:-$USER}"
    
    echo -e "${CYAN}Installation can be performed for:${NC}\n"
    echo -e "  ${YELLOW}1)${NC} Current user: ${GREEN}$CURRENT_USER${NC}"
    echo -e "     - Software and projects will be installed in /home/$CURRENT_USER"
    echo -e "     - SSH keys and Composer will be set up for this user"
    echo -e "     - User will be added to www-data group for shared access"
    echo ""
    echo -e "  ${YELLOW}2)${NC} Another user (specify username)"
    echo -e "     - Useful for setting up a separate development user"
    echo -e "     - User will be created if it doesn't exist"
    echo -e "     - All development tools will be configured for that user"
    echo ""
    echo -e "${CYAN}───────────────────────────────────────────────────────────${NC}\n"
    
    while true; do
        read -p "Your choice (1 or 2): " user_choice
        
        case $user_choice in
            1)
                TARGET_USER="$CURRENT_USER"
                print_success "Installation will be performed for user: $TARGET_USER"
                sleep 1
                break
                ;;
            2)
                while true; do
                    read -p "Enter username: " custom_user
                    if [ -z "$custom_user" ]; then
                        print_error "Username cannot be empty"
                    elif [[ ! "$custom_user" =~ ^[a-z_][a-z0-9_-]*$ ]]; then
                        print_error "Invalid username format (use lowercase letters, numbers, underscore, hyphen)"
                    else
                        TARGET_USER="$custom_user"
                        print_success "Installation will be performed for user: $TARGET_USER"
                        sleep 1
                        break 2
                    fi
                done
                ;;
            *)
                print_error "Invalid choice. Please enter 1 or 2"
                ;;
        esac
    done
}

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
    echo -e "  1. Log out and back in: ${YELLOW}source ~/.bashrc${NC}"
    echo -e "  2. Check Nginx: ${YELLOW}sudo systemctl status nginx${NC}"
    echo -e "  3. Check Horizon: ${YELLOW}sudo supervisorctl status${NC}"
}

# Check for --all flag (skip menu)
if [ "$1" == "--all" ] || [ "$1" == "-a" ]; then
    # Prompt for user selection even in --all mode
    prompt_for_user
    select_all
    run_installation
    exit 0
fi

# Prompt for user selection before showing menu
prompt_for_user

# Interactive menu
while true; do
    display_menu
    read -p "Your choice: " choice
    
    case $choice in
        1) toggle_component "system" ;;
        2) toggle_component "php" ;;
        3) toggle_component "nodejs" ;;
        4) toggle_component "database" ;;
        5) toggle_component "nginx" ;;
        6) toggle_component "devtools" ;;
        7) toggle_component "antigravity" ;;
        8) toggle_component "projects" ;;
        a|A) select_all ;;
        n|N) select_none ;;
        s|S) run_installation; exit 0 ;;
        q|Q) echo "Exiting..."; exit 0 ;;
        *) print_error "Invalid choice" ;;
    esac
done
