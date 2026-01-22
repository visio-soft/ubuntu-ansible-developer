# Ubuntu Developer Setup

Ansible playbooks for setting up a Laravel development environment on **Ubuntu 24.04**.

## 🚀 Quick Start

The installation script will first ask you to choose the target user for installation:
- **Option 1**: Install for the current user (recommended)
- **Option 2**: Install for another user (useful for creating a separate development user)

```bash
git clone https://github.com/visio-soft/ubuntu-ansible-developer.git
cd ubuntu-ansible-developer
chmod +x run.sh
./run.sh
```

**One-liner:**

```bash
git clone https://github.com/visio-soft/ubuntu-ansible-developer.git && cd ubuntu-ansible-developer && chmod +x run.sh && ./run.sh
```

## 📁 File Structure

| File | Description |
|------|-------------|
| `software.yml` | Software installation (PHP, Node, DB, IDE) |
| `projects.yml` | Project setup (clone, migrate, horizon) |
| `run.sh` | Interactive installation script |

## 👤 User Selection

During installation, you'll be prompted to choose the target user:

**Option 1 - Current User:**
- Installs everything for the user running the script
- Recommended for personal development environments
- All tools configured in your home directory

**Option 2 - Another User:**
- Specify a different username for installation
- User will be created if it doesn't exist
- Useful for:
  - Creating a dedicated development user
  - Setting up environments for team members
  - Separating development from your main user account

## 🎛️ Installation Menu


<img width="428" height="342" alt="image" src="https://github.com/user-attachments/assets/1eba7608-ef68-4c54-aab3-1ce995a42a84" />



All components are selected by default:

```
[1] ✓ System Packages (git, curl, acl, supervisor)
[2] ✓ PHP 8.4 + Composer + Extensions
[3] ✓ Node.js 20 + NPM
[4] ✓ PostgreSQL + Redis
[5] ✓ Nginx
[6] ✓ VS Code + DBeaver
[7] ✓ Google Antigravity Editor
[8] ✓ Project Setup

[a] Select All  [n] Select None  [s] Start  [q] Quit
```

## ⚡ Quick Install (No Menu)

```bash
./run.sh --all
```

## ⚙️ Project Configuration

Edit `projects.yml`:

```yaml
projects:
  - { name: "myapp", repo: "git@github.com:user/repo.git", db: "myapp_db", user: "myapp_user" }
```

**Projects directory:** `/var/www/projects` (accessible by all www-data users)

## 📊 Post Installation

```bash
sudo systemctl status nginx      # Check Nginx
sudo supervisorctl status         # Check Horizon
```

Projects available at: `http://project.test`
