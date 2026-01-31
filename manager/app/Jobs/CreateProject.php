<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class CreateProject implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $name;
    protected $repo;
    protected $installHorizon;
    protected $runDeployment;

    public function __construct(string $name, ?string $repo = null, bool $installHorizon = false, bool $runDeployment = false)
    {
        $this->name = $name;
        $this->repo = $repo;
        $this->installHorizon = $installHorizon;
        $this->runDeployment = $runDeployment;
    }

    public function handle(): void
    {
        $name = $this->name;
        $projectsDir = "/var/www/projects";
        $projectPath = "$projectsDir/$name";
        $php = '/usr/bin/php';
        $composer = '/usr/local/bin/composer';
        if (!file_exists($composer)) $composer = '/usr/bin/composer';
        
        Log::info("Job Start: Creating $name (Repo: " . ($this->repo ?: 'None') . ", Horizon: " . ($this->installHorizon?'Yes':'No') . ", Deployment: " . ($this->runDeployment?'Yes':'No') . ")");

        try {
            // 1. Create Folder / Clone
            if ($this->repo) {
                // Clone
                Log::info("Cloning {$this->repo}...");
                $res = Process::path($projectsDir)->run(['git', 'clone', $this->repo, $name]);
                if ($res->failed()) throw new \Exception("Git Clone Failed: " . $res->errorOutput());
                
                // Install Dependencies
                Log::info("Installing Composer Dependencies...");
                Process::path($projectPath)
                    ->env(['HOME' => '/home/alp', 'COMPOSER_HOME' => '/home/alp/.composer'])
                    ->run([$php, '-d', 'memory_limit=-1', $composer, 'install'], function ($type, $output) {
                        Log::info($output);
                    });
            } else {
                // Create New
                Log::info("Running create-project laravel/laravel...");
                $res = Process::path($projectsDir)
                    ->env(['HOME' => '/home/alp', 'COMPOSER_HOME' => '/home/alp/.composer'])
                    ->run([$composer, 'create-project', 'laravel/laravel', $name]);
                if ($res->failed()) throw new \Exception("Composer Create Failed: " . $res->errorOutput());
            }

            // 2. Configure Nginx
            Log::info("Configuring Nginx...");
            $nginxConfig = <<<EOF
server {
    listen 80;
    listen [::]:80;
    server_name {$name}.test;
    root {$projectPath}/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;

    charset utf-8;

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/run/php/php8.4-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        fastcgi_split_path_info ^(.+\.php)(/.+)$;
        fastcgi_index index.php;
        include fastcgi_params;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
EOF;
            $tmpPath = "/tmp/{$name}.nginx";
            file_put_contents($tmpPath, $nginxConfig);
            Process::run("sudo mv {$tmpPath} /etc/nginx/sites-available/{$name}.test");
            Process::run("sudo ln -sf /etc/nginx/sites-available/{$name}.test /etc/nginx/sites-enabled/{$name}.test");
            Process::run("sudo systemctl reload nginx");

            // 3. Update Hosts
             Process::run("echo '127.0.0.1 {$name}.test' | sudo tee -a /etc/hosts");

            // 4. Database
            Log::info("Setting up Database...");
            Process::run(["sudo", "-u", "postgres", "psql", "-c", "CREATE DATABASE \"{$name}\";"]);
            Process::run(["sudo", "-u", "postgres", "psql", "-c", "CREATE USER \"{$name}\" WITH PASSWORD 'secret';"]);
            Process::run(["sudo", "-u", "postgres", "psql", "-c", "GRANT ALL PRIVILEGES ON DATABASE \"{$name}\" TO \"{$name}\";"]);
            Process::run(["sudo", "-u", "postgres", "psql", "-c", "ALTER DATABASE \"{$name}\" OWNER TO \"{$name}\";"]);

            // 5. Env
            Log::info("Updating .env...");
            if (!file_exists("$projectPath/.env")) {
                if (file_exists("$projectPath/.env.example")) {
                    copy("$projectPath/.env.example", "$projectPath/.env");
                }
            }
            
            if (file_exists("$projectPath/.env")) {
                $envContent = file_get_contents("$projectPath/.env");
                $envContent = preg_replace('/^DB_CONNECTION=.*$/m', 'DB_CONNECTION=pgsql', $envContent);
                $envContent = preg_replace('/^#? ?DB_HOST=.*$/m', 'DB_HOST=127.0.0.1', $envContent);
                $envContent = preg_replace('/^#? ?DB_PORT=.*$/m', 'DB_PORT=5432', $envContent);
                $envContent = preg_replace('/^#? ?DB_DATABASE=.*$/m', "DB_DATABASE={$name}", $envContent);
                $envContent = preg_replace('/^#? ?DB_USERNAME=.*$/m', "DB_USERNAME={$name}", $envContent);
                $envContent = preg_replace('/^#? ?DB_PASSWORD=.*$/m', 'DB_PASSWORD=secret', $envContent);
                $envContent = preg_replace("|^APP_URL=.*|m", "APP_URL=http://{$name}.test", $envContent);
                file_put_contents("$projectPath/.env", $envContent);
                
                // Key Generate
                Process::path($projectPath)->run(['php', 'artisan', 'key:generate']);
            }

            // 6. Horizon
            if ($this->installHorizon) {
                Log::info("Installing Horizon...");
                Process::path($projectPath)
                    ->env(['HOME' => '/home/alp', 'COMPOSER_HOME' => '/home/alp/.composer'])
                    ->run([$php, $composer, 'require', 'laravel/horizon']);
                Process::path($projectPath)->run(['php', 'artisan', 'horizon:install']);
                
                // Supervisor Config
                $svConfig = <<<EOF
[program:{$name}-horizon]
process_name=%(program_name)s
command={$php} {$projectPath}/artisan horizon
autostart=true
autorestart=true
user=alp
redirect_stderr=true
stdout_logfile={$projectPath}/storage/logs/horizon.log
stopwaitsecs=3600
EOF;
                $tmpSv = "/tmp/{$name}-horizon.conf";
                file_put_contents($tmpSv, $svConfig);
                Process::run("sudo mv {$tmpSv} /etc/supervisor/conf.d/{$name}-horizon.conf");
                Process::run("sudo supervisorctl reread");
                Process::run("sudo supervisorctl update");
                Process::run("sudo supervisorctl start {$name}-horizon");
            }

            // 7. Deployment Steps (Optional)
            if ($this->runDeployment) {
                Log::info("Running Laravel deployment steps...");
                
                // Run migrations
                Log::info("Running migrations...");
                Process::path($projectPath)->run(['php', 'artisan', 'migrate', '--force']);
                
                // Storage link
                Log::info("Creating storage link...");
                Process::path($projectPath)->run(['php', 'artisan', 'storage:link']);
                
                // NPM install and build
                Log::info("Installing NPM dependencies...");
                Process::path($projectPath)->run(['npm', 'install']);
                
                // Optional: Cache clear
                Process::path($projectPath)->run(['php', 'artisan', 'config:cache']);
                Process::path($projectPath)->run(['php', 'artisan', 'route:cache']);
            }

            // 8. Finalize - Set Permissions
            Process::run(["sudo", "chown", "-R", "alp:www-data", "$projectPath"]);
            Process::run(["sudo", "chmod", "-R", "775", "$projectPath/storage"]);
            Process::run(["sudo", "chmod", "-R", "775", "$projectPath/bootstrap/cache"]);

            Log::info("Project $name setup complete.");

        } catch (\Exception $e) {
            Log::error("Setup Failed for $name: " . $e->getMessage());
        }
    }
}
