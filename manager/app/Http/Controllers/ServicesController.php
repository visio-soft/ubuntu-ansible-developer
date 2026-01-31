<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;
use Illuminate\Support\Facades\File;

class ServicesController extends Controller
{
    private $services = [
        'nginx' => 'Nginx',
        'php8.4-fpm' => 'PHP 8.4 FPM',
        'postgresql' => 'PostgreSQL',
        'redis-server' => 'Redis',
    ];

    private $logs = [
        'nginx' => '/var/log/nginx/error.log',
        'php' => '/var/log/php8.4-fpm.log',
        'redis' => '/var/log/redis/redis-server.log', 
        'postgres' => '/var/log/postgresql/postgresql-14-main.log', 
    ];

    public function index()
    {
        $status = [];
        foreach ($this->services as $service => $label) {
            $res = Process::run("systemctl is-active $service");
            $status[$service] = [
                'label' => $label,
                'active' => trim($res->output()) === 'active',
            ];
        }

        // Projects for logs
        $projects = [];
        $projectsDir = '/var/www/projects';
        if (File::exists($projectsDir)) {
            foreach (File::directories($projectsDir) as $dir) {
                if (basename($dir) === 'ubuntu-ansible-developer') continue;
                $projects[] = basename($dir);
            }
        }

        return view('services.index', compact('status', 'projects'));
    }

    public function restart(Request $request) 
    {
        $service = $request->input('service');
        if (!array_key_exists($service, $this->services)) {
            return back()->with('error', 'Invalid service');
        }

        Process::run("sudo systemctl restart $service");
        sleep(1);

        return back()->with('success', "Restarted {$this->services[$service]}");
    }

    public function logs(Request $request, $type = 'nginx')
    {
        $logPath = '';
        $title = ucfirst($type);

        if ($type === 'project') {
            $project = $request->input('project');
            $logPath = "/var/www/projects/{$project}/storage/logs/laravel.log";
            $title = $project;
        } elseif (isset($this->logs[$type])) {
            $logPath = $this->logs[$type];
            if ($type === 'postgres') {
                $files = glob('/var/log/postgresql/postgresql-*-main.log');
                if (!empty($files)) {
                    $logPath = end($files);
                }
            }
        } else {
            return redirect()->route('services.index');
        }

        $logs = [];
        if ($logPath && File::exists($logPath)) {
            // Read last 2000 lines for infinite scrolling feel
            // Use sudo only for system logs (owned by root), project logs are www-data readable
            $prefix = ($type === 'project') ? '' : 'sudo ';
            $cmd = "{$prefix}tail -n 2000 " . escapeshellarg($logPath);
            
            $res = Process::run($cmd);
            $content = $res->output();
            
            // Parse if it's a Laravel log
            if ($type === 'project') {
                $parsed = $this->parseLaravelLog($content);
                if (count($parsed) > 0) {
                    $logs = $parsed;
                } else {
                    // Fallback to raw if parsing failed but we have content
                    $logs = [['raw' => empty($content) ? 'Log file is empty or could not be read.' : $content]];
                }
            } else {
                // Return generic raw logs wrapped in object
                $logs = [['raw' => $content]];
            }
        }

        if ($request->has('json')) {
            return response()->json($logs);
        }

        return view('services.logs', compact('logs', 'title', 'type', 'logPath'));
    }

    private function parseLaravelLog($content)
    {
        $lines = explode("\n", $content);
        $parsed = [];
        $currentEntry = null;

        foreach ($lines as $line) {
            // Match: [2024-01-22 13:18:35] local.ERROR: Message...
            // Added [\w-]+ to allow hyphens in env name
            if (preg_match('/^\[(?<date>\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2})\] (?<env>[\w-]+)\.(?<level>\w+): (?<message>.*)/', $line, $matches)) {
                if ($currentEntry) {
                    // Push previous entry (latest first)
                    array_unshift($parsed, $currentEntry);
                }
                $currentEntry = [
                    'date' => $matches['date'],
                    'env' => $matches['env'],
                    'level' => strtoupper($matches['level']),
                    'message' => $matches['message'],
                    'context' => '',
                    'stack_trace' => ''
                ];
            } else {
                if ($currentEntry) {
                    // It's a continuation/stack trace
                    $currentEntry['stack_trace'] .= $line . "\n";
                }
            }
        }
        if ($currentEntry) {
            array_unshift($parsed, $currentEntry);
        }

        return $parsed;
    }

    public function phpIni()
    {
        $path = '/etc/php/8.4/fpm/php.ini';
        $content = '';
        
        if (File::exists($path)) {
            $content = file_get_contents($path);
        } else {
            $res = Process::run("php --ini");
            $content = "; Could not read $path directly. \n; Output of php --ini:\n" . $res->output();
        }

        return view('services.php', compact('content', 'path'));
    }

    public function savePhpIni(Request $request)
    {
        $content = $request->input('content');
        $path = '/etc/php/8.4/fpm/php.ini';

        $tempFile = tempnam(sys_get_temp_dir(), 'phpini');
        file_put_contents($tempFile, $content);

        Process::run("sudo cp $tempFile $path");
        Process::run("sudo systemctl restart php8.4-fpm");
        
        unlink($tempFile);

        return back()->with('success', 'PHP.ini updated and PHP-FPM restarted.');
    }

    public function clearLogs(Request $request)
    {
        $type = $request->input('type');
        $logPath = '';

        if ($type === 'project') {
            $project = $request->input('project');
            $logPath = "/var/www/projects/{$project}/storage/logs/laravel.log";
        } elseif (isset($this->logs[$type])) {
            $logPath = $this->logs[$type];
        }

        if ($logPath && File::exists($logPath)) {
            try {
                $prefix = (str_starts_with($logPath, '/var/log')) ? 'sudo ' : '';
                $result = Process::run("{$prefix}truncate -s 0 " . escapeshellarg($logPath));
                
                if ($result->failed()) {
                    return back()->with('error', 'Failed to clear logs: ' . $result->errorOutput());
                }
                
                // Verify the file was actually cleared
                clearstatcache(true, $logPath);
                if (File::size($logPath) === 0) {
                    return back()->with('success', 'Logs cleared successfully.');
                } else {
                    return back()->with('error', 'Log file still contains data after clear attempt.');
                }
            } catch (\Exception $e) {
                return back()->with('error', 'Error clearing logs: ' . $e->getMessage());
            }
        }

        return back()->with('error', 'Log file not found.');
    }
}
