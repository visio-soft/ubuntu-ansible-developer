<?php

namespace App\Http\Controllers;

use App\Jobs\CreateProject;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\Log;

class SiteController extends Controller
{
    public function index()
    {
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

                $sites[] = [
                    'name' => $name,
                    'url' => $url,
                    'path' => $path,
                    'type' => $isLaravel ? 'Laravel' : 'Static',
                    'version' => $laravelVersion,
                    'horizon' => $horizonStatus,
                    'db_name' => $dbName,
                    'env_exists' => $envExists
                ];
            }
        }

        return view('sites.index', compact('sites'));
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
        // Check if it's SSH
        if (!str_starts_with($repo, 'git@')) {
            return ['status' => 'ok']; // naive check for https
        }

        // Test SSH connection to host (usually github.com)
        preg_match('/@(.*):/', $repo, $matches);
        $host = $matches[1] ?? 'github.com';

        $keyPath = '/home/alp/.ssh/id_ed25519';
        // Use IdentitiesOnly=yes and explicit key path to avoid picking up the readonly key
        $cmd = "sudo -u alp ssh -T -o BatchMode=yes -o StrictHostKeyChecking=no -o IdentitiesOnly=yes -i $keyPath git@$host 2>&1";
        $result = Process::run($cmd);
        $output = $result->output();

        if (str_contains($output, 'successfully authenticated')) {
            return ['status' => 'ok'];
        } else {
            return [
                'status' => 'error',
                'message' => 'Access denied: ' . trim($output),
                'key_guide' => 'Please add the public key below to your GitHub account.',
                'public_key' => $this->getPublicKey()
            ];
        }
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

        $path = "/var/www/projects/{$name}";

        if (File::exists($path)) {
            return back()->with('error', 'Project path already exists.');
        }

        // Final check before dispatching
        $access = $this->verifyGitAccess($repo);
        if ($access['status'] !== 'ok') {
            return back()->with('error', 'Git access denied. Please verify your SSH key before creating.');
        }

        // Dispatch Job with new params
        CreateProject::dispatch($name, $repo, $installHorizon);

        return back()->with('success', "Project installation started for '$name'. Check Horizon status later.");
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
}
