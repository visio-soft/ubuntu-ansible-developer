# Ubuntu Developer Setup

Ansible playbooks for setting up a **Laravel Sail (Docker)** development environment on **Ubuntu 24.04**.

## 🚀 Quick Start

The installation script will automatically install Docker and configure Laravel Sail for the current user.

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
| `software.yml` | Software installation (Docker, Node, IDE) |
| `projects.yml` | Project setup with Laravel Sail (clone, configure, containers) |
| `run.sh` | Interactive installation script |

## 🎛️ Installation Menu


<img width="428" height="342" alt="image" src="https://github.com/user-attachments/assets/1eba7608-ef68-4c54-aab3-1ce995a42a84" />



All components are selected by default:

```
[1] ✓ System Packages (git, curl, acl, supervisor)
[2] ✓ Docker + Docker Compose (for Laravel Sail)
[3] ✓ Node.js 20 + NPM
[4] ✓ VS Code + DBeaver
[5] ✓ Google Antigravity Editor
[6] ✓ Project Setup (Sail, containers, migrate)

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
  - { name: "myapp", repo: "git@github.com:user/repo.git" }
```

**Projects directory:** `/var/www/projects`

## 🐳 Using Laravel Sail

After installation, each project will have Laravel Sail configured:

```bash
cd /var/www/projects/myapp

# Start containers (PHP, PostgreSQL, Redis)
./vendor/bin/sail up -d

# Stop containers
./vendor/bin/sail down

# Run artisan commands
./vendor/bin/sail artisan migrate
./vendor/bin/sail artisan tinker

# Run composer
./vendor/bin/sail composer install

# Run npm
./vendor/bin/sail npm install
./vendor/bin/sail npm run dev

# Access container shell
./vendor/bin/sail shell

# View logs
./vendor/bin/sail logs
```

## 📊 Post Installation

```bash
# Check Docker status
docker ps

# Check containers for a project
cd /var/www/projects/zone
./vendor/bin/sail ps
```

**Projects available at:**
- zone: `http://localhost:8000`
- gate: `http://localhost:8001`

## 🔧 Sail Configuration

Laravel Sail uses Docker Compose under the hood. The default configuration includes:
- **PHP 8.x** (latest Laravel compatible version)
- **PostgreSQL** database
- **Redis** for cache and queues
- **Nginx** web server (inside container)

All services run in isolated Docker containers, making it easy to have consistent development environments.
