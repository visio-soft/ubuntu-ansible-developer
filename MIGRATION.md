# Migration Guide: Native Setup to Laravel Sail

This guide helps you migrate from the old native PHP/Nginx setup to the new Docker-based Laravel Sail environment.

## What Changed?

### Before (Native Setup)
- Native PHP 8.4 + PHP-FPM installation
- Native Nginx web server
- Native PostgreSQL database
- Native Redis server
- Projects accessible at `http://project.test`

### After (Laravel Sail)
- Docker containers for all services
- PHP CLI on host (for initial setup only)
- All services run in isolated containers
- Projects accessible at `http://localhost:PORT`

## Migration Steps

### 1. Backup Your Existing Setup

If you have an existing installation, backup your data:

```bash
# Backup databases
sudo -u postgres pg_dumpall > ~/backup_databases.sql

# Backup project files (if needed)
cp -r /var/www/projects ~/projects_backup
```

### 2. Clean Up Old Installation (Optional)

If you want to remove the old native services:

```bash
# Stop and disable services
sudo systemctl stop nginx php8.4-fpm postgresql redis-server supervisor
sudo systemctl disable nginx php8.4-fpm postgresql redis-server

# Remove packages (optional - be careful!)
# sudo apt remove nginx php8.4-fpm postgresql redis-server
```

### 3. Run the New Installation

```bash
cd ubuntu-ansible-developer
git pull origin main
./run.sh --all
```

### 4. Restore Database Data (if needed)

After Sail containers are running:

```bash
cd /var/www/projects/your-project

# Copy backup into container
docker cp ~/backup_databases.sql $(docker ps -qf "name=pgsql"):/tmp/

# Restore in container
./vendor/bin/sail exec pgsql psql -U sail -d postgres -f /tmp/backup_databases.sql
```

## Key Differences

### Starting/Stopping Projects

**Before:**
```bash
sudo systemctl restart nginx
sudo supervisorctl restart project-horizon
```

**After:**
```bash
cd /var/www/projects/project-name
./vendor/bin/sail up -d      # Start
./vendor/bin/sail down        # Stop
./vendor/bin/sail restart     # Restart
```

### Running Commands

**Before:**
```bash
cd /var/www/projects/project-name
php artisan migrate
composer install
npm run build
```

**After:**
```bash
cd /var/www/projects/project-name
./vendor/bin/sail artisan migrate
./vendor/bin/sail composer install
./vendor/bin/sail npm run build
```

### Accessing Services

**Before:**
- Web: `http://project.test`
- Database: `localhost:5432` (host)
- Redis: `localhost:6379` (host)

**After:**
- Web: `http://localhost:8000` (zone), `http://localhost:8001` (gate)
- Database: `localhost:54XX` (forwarded from container)
- Redis: `localhost:63XX` (forwarded from container)

### Database Connections

**Before (.env):**
```env
DB_HOST=127.0.0.1
DB_DATABASE=project_db
DB_USERNAME=project_user
DB_PASSWORD=secret
```

**After (.env):**
```env
DB_HOST=pgsql
DB_DATABASE=project_db
DB_USERNAME=sail
DB_PASSWORD=password
```

## Troubleshooting

### Port Conflicts

If ports 8000-8001 are in use:

```bash
# Edit .env and change APP_PORT
APP_PORT=9000

# Restart Sail
./vendor/bin/sail down
./vendor/bin/sail up -d
```

### Docker Group Permission

If you get "permission denied" errors:

```bash
# Apply docker group (logout/login or use newgrp)
newgrp docker

# Or logout and login again
```

### Container Won't Start

Check logs:

```bash
./vendor/bin/sail logs
docker logs container-name
```

## Benefits of Sail

1. **Isolation**: Each project runs in its own containers
2. **Consistency**: Same environment on all machines
3. **Flexibility**: Easy to switch PHP/database versions
4. **No Conflicts**: Multiple projects can run simultaneously
5. **Easy Cleanup**: Just remove containers, no system-wide changes

## Getting Help

- Laravel Sail Documentation: https://laravel.com/docs/12.x/sail
- Docker Documentation: https://docs.docker.com/
- Project Repository: https://github.com/visio-soft/ubuntu-ansible-developer
