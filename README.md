# Ubuntu Developer Setup

Ansible playbooks for setting up a Laravel development environment on **Ubuntu 24.04**.

## 🚀 Quick Start

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

## 🎛️ Installation Menu


<img width="518" height="390" alt="image" src="https://github.com/user-attachments/assets/74be8482-5822-40a3-90c9-ff727db887c9" />

All components are selected by default:

```
[1] ✓ System Packages (git, curl, acl, supervisor)
[2] ✓ PHP 8.4 + Composer + Extensions
[3] ✓ Node.js 20 + NPM
[4] ✓ PostgreSQL + Redis
[5] ✓ Nginx + Valet Linux
[6] ✓ VS Code + DBeaver
[7] ✓ Project Setup

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
valet status                  # Check Valet
sudo supervisorctl status     # Check Horizon
```

Projects available at: `http://project-name.test`
