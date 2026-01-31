<?php

namespace App\Http\Controllers;

use App\Jobs\CreateProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class SiteController extends Controller
{
    private $services = [
        'nginx' => 'Nginx',
        'php8.4-fpm' => 'PHP 8.4 FPM',
        'postgresql' => 'PostgreSQL',
        'redis-server' => 'Redis',
    ];

    public function index()
    {
        // 1. Service Status
        $servicesStatus = [];
        foreach ($this->services as $service => $label) {
            $res = Process::run("systemctl is-active $service");
            $servicesStatus[$service] = [
                'label' => $label,
                'active' => trim($res->output()) === 'active',
            ];
        }

        // 2. List Projects
        $projectsDir = '/var/www/projects';
        $sites = [];

        // 1. List Projects
        if (File::exists($projectsDir)) {
            $directories = File::directories($projectsDir);
            foreach ($directories as $dir) {
                $name = basename($dir);
                if ($name === 'ubuntu-ansible-developer') continue; 
                
                $url = "http://{$name}.test";
                $path = $dir;
                
                $isLaravel = File::exists("$dir/artisan");
                $laravelVersion = $isLaravel ? $this->getLaravelVersion($dir) : null;
                $dbName = 'N/A';
                $envExists = false;
                $horizonStatus = 'N/A';

                if ($isLaravel) {
                    $envPath = "$dir/.env";
                    if (File::exists($envPath)) {
                        $envExists = true;
                        $envContent = File::get($envPath);
                        preg_match('/DB_DATABASE=(.*)/', $envContent, $dbMatches);
                        $dbName = isset($dbMatches[1]) ? trim($dbMatches[1]) : 'Unknown';
                    }

                    // Supervisor check
                    $res = Process::run("sudo supervisorctl status | grep {$name}-horizon");
                    if (str_contains($res->output(), 'RUNNING')) {
                        $horizonStatus = 'running';
                    } else {
                        if (File::exists("/etc/supervisor/conf.d/{$name}-horizon.conf")) {
                            $horizonStatus = 'stopped';
                        }
                    }
                }

                // Count ERROR/WARNING logs for Laravel projects
                $logCount = 0;
                if ($isLaravel) {
                    $logFile = "$dir/storage/logs/laravel.log";
                    if (File::exists($logFile)) {
                        $cmd = "grep -cE '^\[.*\] \w+\.(ERROR|WARNING|CRITICAL):' " . escapeshellarg($logFile) . " 2>/dev/null || echo 0";
                        $res = Process::run($cmd);
                        $logCount = (int) trim($res->output());
                    }
                }

                $sites[] = [
                    'name' => $name,
                    'url' => $url,
                    'path' => $path,
                    'type' => $isLaravel ? 'Laravel' : 'Static',
                    'version' => $laravelVersion,
                    'horizon' => $horizonStatus,
                    'db_name' => $dbName,
                    'env_exists' => $envExists,
                    'log_count' => $logCount
                ];
            }
        }

        return view('sites.index', [
            'sites' => $sites,
            'servicesStatus' => $servicesStatus
        ]);
    }
    
    public function restartService(Request $request) 
    {
        $service = $request->input('service');
        if (!array_key_exists($service, $this->services)) {
            return back()->with('error', 'Invalid service');
        }

        Process::run("sudo systemctl restart $service");
        sleep(1);

        return back()->with('success', "Restarted {$this->services[$service]}");
    }

    public function checkGit(Request $request)
    {
        $repo = $request->query('repo');
        if (!$repo) return response()->json(['status' => 'error', 'message' => 'Repo missing']);

        $result = $this->verifyGitAccess($repo);

        return response()->json($result);
    }

    private function verifyGitAccess($repo)
    {
        // Basic format validation
        if (empty($repo) || strlen($repo) < 10) {
            return [
                'status' => 'error',
                'message' => 'Invalid repository URL format',
                'public_key' => $this->getPublicKey()
            ];
        }

        // Check if it's SSH format (git@github.com:user/repo.git)
        if (str_starts_with($repo, 'git@')) {
            // Extract host from SSH URL
            if (!preg_match('/^git@([^:]+):(.+)\.git$/', $repo, $matches)) {
                return [
                    'status' => 'error',
                    'message' => 'Invalid SSH URL format. Expected: git@github.com:user/repo.git',
                    'public_key' => $this->getPublicKey()
                ];
            }
            
            $host = $matches[1];
            
            // Test SSH connection
            $keyPath = '/home/alp/.ssh/id_ed25519';
            $cmd = "sudo -u alp ssh -T -o BatchMode=yes -o StrictHostKeyChecking=no -o IdentitiesOnly=yes -i $keyPath git@$host 2>&1";
            $result = Process::run($cmd);
            $output = $result->output();

            if (str_contains($output, 'successfully authenticated')) {
                return ['status' => 'ok'];
            } else {
                return [
                    'status' => 'error',
                    'message' => 'SSH access denied. Please add the public key to your Git provider.',
                    'public_key' => $this->getPublicKey()
                ];
            }
        }
        
        // Check if it's HTTPS format
        if (str_starts_with($repo, 'https://')) {
            if (!filter_var($repo, FILTER_VALIDATE_URL)) {
                return [
                    'status' => 'error',
                    'message' => 'Invalid HTTPS URL format',
                    'public_key' => $this->getPublicKey()
                ];
            }
            
            // For HTTPS, we can't easily verify access without credentials
            // So we just validate the format
            return ['status' => 'ok'];
        }

        // Invalid format
        return [
            'status' => 'error',
            'message' => 'Repository URL must start with git@ or https://',
            'public_key' => $this->getPublicKey()
        ];
    }

    private function getPublicKey()
    {
        $keys = [
            '/home/alp/.ssh/id_ed25519.pub',
            '/home/alp/.ssh/id_rsa.pub',
            '/home/alp/.ssh/github_readonly.pub'
        ];

        foreach ($keys as $path) {
            $cmd = "sudo cat $path 2>/dev/null";
            $res = Process::run($cmd);
            if ($res->successful() && !empty(trim($res->output()))) {
                return trim($res->output());
            }
        }

        // Final attempt checking what's actually there
        $ls = Process::run("sudo ls /home/alp/.ssh/*.pub");
        if ($ls->successful()) {
            return "No common key found. Files in .ssh: " . str_replace("\n", ", ", trim($ls->output()));
        }

        return "Could not access /home/alp/.ssh directory even with sudo.";
    }

    private function getLaravelVersion($path)
    {
        try {
            $content = @file_get_contents("$path/composer.json");
            if (!$content) return 'Unknown';
            $json = json_decode($content, true);
            $v = $json['require']['laravel/framework'] ?? 'Unknown';
            return str_replace(['^', '~'], '', $v);
        } catch (\Exception $e) {
            return 'Unknown';
        }
    }

    public function editEnv($site)
    {
        $path = "/var/www/projects/{$site}/.env";
        if (!File::exists($path)) {
            return back()->with('error', '.env file not found.');
        }

        // We can read normally if permissions allow, but safer to use sudo cat if we have it
        $content = shell_exec("sudo cat " . escapeshellarg($path));
        
        return view('sites.env', [
            'site' => $site,
            'content' => $content,
            'path' => $path
        ]);
    }

    public function saveEnv(Request $request, $site)
    {
        $path = "/var/www/projects/{$site}/.env";
        $content = $request->input('content');

        // Write via sudo tee
        $tmp = tempnam(sys_get_temp_dir(), 'env');
        file_put_contents($tmp, $content);
        
        $cmd = "sudo cp " . escapeshellarg($tmp) . " " . escapeshellarg($path);
        $res = Process::run($cmd);
        
        unlink($tmp);

        if ($res->successful()) {
            return redirect()->route('sites.index')->with('success', '.env updated for ' . $site);
        }

        return back()->with('error', 'Failed to save .env: ' . $res->errorOutput());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|alpha_dash',
            'repo' => 'required|string',
        ]);
        
        $name = $request->name;
        $repo = $request->input('repo');
        $installHorizon = $request->has('horizon');
        $runDeployment = $request->has('deployment');
        $createDatabase = $request->has('database');

        $path = "/var/www/projects/{$name}";

        if (File::exists($path)) {
            return back()->with('error', 'Project path already exists.');
        }

        // Final check before dispatching
        $access = $this->verifyGitAccess($repo);
        if ($access['status'] !== 'ok') {
            return back()->with('error', 'Git access denied. Please verify your SSH key before creating.');
        }

        // Dispatch Job with all params
        CreateProject::dispatch($name, $repo, $installHorizon, $runDeployment, $createDatabase);

        return redirect()->route('sites.installation-logs')
            ->with('success', "Project installation started for '$name'. Follow the logs below.");
    }

    public function destroy($site)
    {
        // Safety check: don't delete the manager itself
        if ($site === 'ubuntu-ansible-developer') {
            return back()->with('error', 'Cannot delete the manager project.');
        }

        $projectPath = "/var/www/projects/{$site}";
        
        Log::info("Deleting project: $site");

        try {
            // 1. Stop and remove Supervisor process
            Process::run("sudo supervisorctl stop {$site}-horizon");
            Process::run("sudo rm -f /etc/supervisor/conf.d/{$site}-horizon.conf");
            Process::run("sudo supervisorctl update");

            // 2. Remove Nginx config
            Process::run("sudo rm -f /etc/nginx/sites-enabled/{$site}.test");
            Process::run("sudo rm -f /etc/nginx/sites-available/{$site}.test");
            Process::run("sudo systemctl reload nginx");

            // 3. Remove /etc/hosts entry
            Process::run("sudo sed -i \"/{$site}.test/d\" /etc/hosts");

            // 4. Remove project directory
            if (File::exists($projectPath)) {
                Process::run("sudo rm -rf " . escapeshellarg($projectPath));
            }

            return redirect()->route('sites.index')->with('success', "Project '$site' deleted successfully (Database preserved).");
        } catch (\Exception $e) {
            Log::error("Failed to delete project $site: " . $e->getMessage());
            return back()->with('error', "Failed to delete project: " . $e->getMessage());
        }
    }

    public function installationLogs()
    {
        return view('sites.installation-logs');
    }

    public function getInstallationLogs()
    {
        $logPath = storage_path('logs/laravel.log');
        $content = '';

        if (File::exists($logPath)) {
            // Get last 500 lines
            $result = Process::run("tail -n 500 " . escapeshellarg($logPath));
            $content = $result->output();
        }

        return response()->json(['content' => $content]);
    }

    public function openInTerminal($site)
    {
        $path = "/var/www/projects/{$site}";
        
        if (!File::exists($path)) {
            return back()->with('error', 'Project not found.');
        }

        // Open GNOME Terminal in the project directory
        // Use sudo -u alp since PHP-FPM runs as www-data but GUI apps need user session
        // DBUS_SESSION_BUS_ADDRESS needed for gnome-terminal to communicate with session bus
        $uid = 1000; // alp user ID
        $cmd = "sudo -u alp DISPLAY=:0 DBUS_SESSION_BUS_ADDRESS=unix:path=/run/user/{$uid}/bus gnome-terminal --working-directory=" . escapeshellarg($path) . " > /dev/null 2>&1 &";
        Process::run($cmd);

        return back()->with('success', 'Terminal opened for ' . $site);
    }

    public function openInFolder($site)
    {
        $path = "/var/www/projects/{$site}";
        
        if (!File::exists($path)) {
            return back()->with('error', 'Project not found.');
        }

        // Use xdg-open (freedesktop.org standard) - works across GNOME, KDE, XFCE, etc.
        // Use sudo -u alp since PHP-FPM runs as www-data but GUI apps need user session
        $cmd = "sudo -u alp DISPLAY=:0 xdg-open " . escapeshellarg($path) . " > /dev/null 2>&1 &";
        Process::run($cmd);

        return back()->with('success', 'Folder opened for ' . $site);
    }
}
