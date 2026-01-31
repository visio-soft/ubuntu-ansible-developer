<?php

namespace App\Http\Controllers;

use App\Jobs\InstallSoftware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Process;

class SoftwareController extends Controller
{
    private $tools = [
        'antigravity' => [
            'name' => 'Google Antigravity Editor',
            'bin' => 'antigravity', 
            'url' => null, 
            'description' => 'Advanced Agentic Coding Editor.',
            'icon' => '🚀'
        ],
        'chrome' => [
            'name' => 'Google Chrome',
            'bin' => 'google-chrome',
            'url' => null,
            'description' => 'Fast, secure web browser.',
            'icon' => '🌐'
        ],
        'code' => [
            'name' => 'VS Code',
            'bin' => 'code',
            'url' => null,
            'description' => 'Code editing. Redefined.',
            'icon' => '📝'
        ],
        'tableplus' => [
            'name' => 'TablePlus',
            'bin' => 'tableplus',
            'url' => null, 
            'description' => 'Modern, native tool for database management.',
            'icon' => '🐘'
        ],
        'dbeaver' => [
            'name' => 'DBeaver',
            'bin' => 'dbeaver-ce', 
            'url' => null,
            'description' => 'Universal Database Tool.',
            'icon' => '🦫'
        ]
    ];

    public function index()
    {
        $software = [];
        foreach ($this->tools as $key => $tool) {
            $isInstalled = false;
            $currentVersion = null;
            $hasUpdate = false;

            // Check if binary exists
            $res = Process::run("which {$tool['bin']}");
            if (!empty(trim($res->output()))) {
                $isInstalled = true;
                
                // Get Version
                if ($key === 'chrome') {
                    $v = Process::run("google-chrome --version");
                    $currentVersion = str_replace('Google Chrome ', '', trim($v->output()));
                } elseif ($key === 'code') {
                    $v = Process::run("code --version");
                    $lines = explode("\n", $v->output());
                    $currentVersion = $lines[0] ?? null;
                } elseif ($key === 'dbeaver') {
                    $v = Process::run("dbeaver-ce --version");
                    $currentVersion = str_replace('DBeaver ', '', trim($v->output()));
                } elseif ($key === 'antigravity') {
                     $v = Process::run("antigravity --version");
                     $lines = explode("\n", $v->output());
                     $currentVersion = $lines[0] ?? null;
                } elseif ($key === 'tableplus') {
                    // TablePlus doesn't have a reliable CLI version flag, check dpkg
                    $v = Process::run("dpkg -s tableplus | grep Version");
                    if ($v->successful()) {
                         $currentVersion = str_replace('Version: ', '', trim($v->output()));
                    }
                }
            }
            
            // Check for updates (naive check via apt-cache policy)
            // This is just a simulation for now or expensive call.
            // Let's rely on manual update or just showing current version.

            $software[] = array_merge($tool, [
                'key' => $key,
                'installed' => $isInstalled,
                'version' => $currentVersion
            ]);
        }

        return view('software.index', compact('software'));
    }

    public function install(Request $request)
    {
        $key = $request->input('software');
        if (!array_key_exists($key, $this->tools)) {
            return back()->with('error', 'Unknown software');
        }

        InstallSoftware::dispatch($key);

        return back()->with('success', "Installation started for {$this->tools[$key]['name']}. It may take a few minutes.");
    }
}
