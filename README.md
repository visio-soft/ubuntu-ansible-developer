# Ubuntu Developer Setup

Ansible playbooks for setting up a **Native Laravel** development environment on **Ubuntu 24.04** with Nginx, PostgreSQL, and Redis.

## 🚀 Quick Start

The installation script will automatically install and configure native services (Nginx, PostgreSQL, Redis, PHP-FPM) for Laravel development.

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
| `software.yml` | Software installation (PHP, Nginx, PostgreSQL, Redis, Node, IDE) |
| `projects.yml` | Project setup (clone, configure, Nginx, Horizon) |
| `run.sh` | Interactive installation script |

## 🎛️ Installation Menu

All components are selected by default:

```
[1] ✓ System Packages (git, curl, acl, supervisor)
[2] ✓ PHP 8.4 + PHP-FPM + Composer
[3] ✓ Nginx Web Server
[4] ✓ PostgreSQL Database
[5] ✓ Redis Server
[6] ✓ Node.js 20 + NPM
[7] ✓ VS Code + DBeaver
[8] ✓ Google Antigravity Editor
[9] ✓ Project Setup (Native Laravel, Nginx, Horizon)

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

**Projects directory:** `/var/www/projects`

## 🌐 Using Native Services

After installation, your projects will be configured with native services:

```bash
cd /var/www/projects/myapp

# Run artisan commands
php artisan migrate
php artisan tinker

# Run composer
composer install
composer update

# Run npm
npm install
npm run dev
npm run build

# Check services
sudo systemctl status nginx
sudo systemctl status postgresql
sudo systemctl status redis-server
sudo systemctl status php8.4-fpm

# Check Laravel Horizon (queue worker)
supervisorctl status
supervisorctl restart myapp-horizon
```

## 📊 Post Installation

```bash
# Check service status
sudo systemctl status nginx postgresql redis-server php8.4-fpm

# Check Horizon status
supervisorctl status

# View Nginx logs
sudo tail -f /var/log/nginx/error.log
sudo tail -f /var/log/nginx/access.log

# View Horizon logs
tail -f /var/www/projects/myapp/storage/logs/horizon.log
```

**Projects available at:**
- zone: `http://zone.test`
- gate: `http://gate.test`

## 🔧 Service Configuration

The setup includes:
- **PHP 8.4** with PHP-FPM
- **Nginx** web server with configured server blocks
- **PostgreSQL** database (latest version)
- **Redis** for cache and queues
- **Supervisor** for managing Laravel Horizon workers
- **Node.js 20** for asset compilation

All services run natively on the system for optimal performance.

## 🗄️ Database Access

Connect to PostgreSQL:

```bash
# Using psql
psql -U zone_user -d zone_db -h 127.0.0.1

# Using DBeaver (GUI)
# Host: 127.0.0.1
# Port: 5432
# Database: zone_db
# Username: zone_user
# Password: secret
```

## 🔄 Service Management

```bash
# Restart services
sudo systemctl restart nginx
sudo systemctl restart postgresql
sudo systemctl restart redis-server
sudo systemctl restart php8.4-fpm

# Reload Nginx (without dropping connections)
sudo systemctl reload nginx

# Restart Horizon workers
supervisorctl restart zone-horizon
supervisorctl restart gate-horizon
```

## 🏗️ Architecture

This setup separates **program installations** from **project installations**:

- **software.yml**: Installs system-wide programs (PHP, Nginx, databases) - independent of any project
- **projects.yml**: Sets up Laravel projects - uses already installed programs

This separation allows you to:
- Install programs once, use for multiple projects
- Update projects without reinstalling programs
- Maintain cleaner, more modular setup
