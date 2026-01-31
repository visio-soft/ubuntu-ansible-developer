<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatabaseController extends Controller
{
    public function index()
    {
        // Lists databases
        // We use the configured pgsql connection
        $databases = DB::select("SELECT datname, pg_database_size(datname) as size FROM pg_database WHERE datistemplate = false;");
        
        // Filter out system dbs if needed
        $dbs = array_filter($databases, function($db) {
            return !in_array($db->datname, ['postgres']);
        });

        return view('databases.index', ['databases' => $dbs]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|alpha_dash',
        ]);

        $name = $request->name;

        try {
            DB::statement("CREATE DATABASE \"{$name}\"");
            return back()->with('success', "Database {$name} created.");
        } catch (\Exception $e) {
            return back()->with('error', $e->getMessage());
        }
    }

    public function createDatabase(Request $request)
    {
        $request->validate([
            'name' => 'required|alpha_dash',
            'password' => 'required|string|min:6'
        ]);

        $name = $request->name;
        $password = $request->password;

        try {
            // Create database
            DB::statement("CREATE DATABASE \"{$name}\"");
            
            // Create user with password
            DB::statement("CREATE USER \"{$name}\" WITH PASSWORD '{$password}'");
            
            // Grant privileges
            DB::statement("GRANT ALL PRIVILEGES ON DATABASE \"{$name}\" TO \"{$name}\"");
            DB::statement("ALTER DATABASE \"{$name}\" OWNER TO \"{$name}\"");

            return back()->with('success', "Database '{$name}' created successfully with user '{$name}'.");
        } catch (\Exception $e) {
            return back()->with('error', 'Failed to create database: ' . $e->getMessage());
        }
    }
}
