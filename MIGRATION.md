# Migration Guide: Laravel Sail to Native Setup

This guide helps you migrate from the old Docker-based Laravel Sail environment to the new native PHP/Nginx setup.

## What Changed?

### Before (Laravel Sail with Docker)
- Docker containers for all services
- PHP CLI on host (for initial setup only)
- All services run in isolated containers
- Projects accessible at `http://localhost:PORT`

### After (Native Setup)
- Native PHP 8.4 + PHP-FPM installation
- Native Nginx web server
- Native PostgreSQL database
- Native Redis server
- Projects accessible at `http://project.test`

## Migration Steps

### 1. Backup Your Existing Setup

If you have an existing Sail installation, backup your data:

```bash
# Backup databases from Sail containers
cd /var/www/projects/zone
./vendor/bin/sail exec pgsql pg_dumpall -U sail > ~/backup_databases.sql

# Backup project files (if needed)
cp -r /var/www/projects ~/projects_backup
```

### 2. Stop and Remove Sail Containers

```bash
# Stop all Sail containers
cd /var/www/projects/zone
./vendor/bin/sail down

cd /var/www/projects/gate
./vendor/bin/sail down

# Remove all Docker containers and images (optional)
docker system prune -a
```

### 3. Remove Sail Dependencies (Optional)

```bash
# Remove laravel/sail from projects
cd /var/www/projects/zone
composer remove laravel/sail --dev
rm -f docker-compose.yml

cd /var/www/projects/gate
composer remove laravel/sail --dev
rm -f docker-compose.yml
```

### 4. Run the New Native Installation

```bash
cd ubuntu-ansible-developer
git pull origin main
./run.sh --all
```

### 5. Restore Database Data (if needed)

After native PostgreSQL is installed:

```bash
# Restore databases
psql -U postgres -h 127.0.0.1 -f ~/backup_databases.sql
```

## Key Differences

### Starting/Stopping Services

**Before (Sail):**
```bash
cd /var/www/projects/project-name
./vendor/bin/sail up -d      # Start
./vendor/bin/sail down        # Stop
./vendor/bin/sail restart     # Restart
```

**After (Native):**
```bash
# Services are always running
sudo systemctl status nginx postgresql redis-server php8.4-fpm

# Restart if needed
sudo systemctl restart nginx
```

### Running Commands

**Before (Sail):**
```bash
cd /var/www/projects/project-name
./vendor/bin/sail artisan migrate
./vendor/bin/sail composer install
./vendor/bin/sail npm run build
```

**After (Native):**
```bash
cd /var/www/projects/project-name
php artisan migrate
composer install
npm run build
```

### Accessing Services

**Before (Sail):**
- Web: `http://localhost:8000` (zone), `http://localhost:8001` (gate)
- Database: `localhost:54XX` (forwarded from container)
- Redis: `localhost:63XX` (forwarded from container)

**After (Native):**
- Web: `http://zone.test`, `http://gate.test`
- Database: `localhost:5432` (native PostgreSQL)
- Redis: `localhost:6379` (native Redis)

### Database Connections

**Before (.env - Sail):**
```env
DB_HOST=pgsql
DB_DATABASE=project_db
DB_USERNAME=sail
DB_PASSWORD=password
```

**After (.env - Native):**
```env
DB_HOST=127.0.0.1
DB_DATABASE=project_db
DB_USERNAME=project_user
DB_PASSWORD=secret
```

## Troubleshooting

### Port Conflicts

If port 80 is already in use:

```bash
# Check what's using port 80
sudo lsof -i :80

# Stop conflicting service
sudo systemctl stop apache2  # If Apache is running
```

### Permission Issues

If you get permission errors:

```bash
# Fix storage permissions
cd /var/www/projects/project-name
sudo chown -R $USER:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache
```

### Nginx Configuration Errors

Test and view Nginx configuration:

```bash
# Test configuration
sudo nginx -t

# View configuration
cat /etc/nginx/sites-available/zone.test

# Reload Nginx
sudo systemctl reload nginx
```

### PostgreSQL Connection Issues

```bash
# Check PostgreSQL status
sudo systemctl status postgresql

# Check if database exists
sudo -u postgres psql -c "\l"

# Create database manually if needed
sudo -u postgres createdb -O project_user project_db
```

## Benefits of Native Setup

1. **Performance**: No Docker overhead, direct access to system resources
2. **Simplicity**: Standard Linux tools and commands
3. **Debugging**: Easier to debug with native tools
4. **Resource Usage**: Lower memory and CPU usage
5. **Integration**: Better integration with system services

## Getting Help

- Laravel Documentation: https://laravel.com/docs
- Nginx Documentation: https://nginx.org/en/docs/
- PostgreSQL Documentation: https://www.postgresql.org/docs/
- Project Repository: https://github.com/visio-soft/ubuntu-ansible-developer
