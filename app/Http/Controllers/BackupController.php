<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class BackupController extends Controller
{
    /**
     * Display list of backups
     */
    public function index()
    {
        $backupDir = storage_path('app/backups');
        $backups = [];
        
        if (is_dir($backupDir)) {
            $files = glob($backupDir . '/*.sql');
            foreach ($files as $file) {
                if (is_file($file)) {
                    $backups[] = (object) [
                        'filename' => basename($file),
                        'path' => $file,
                        'size' => filesize($file),
                        'created_at' => filemtime($file),
                        'formatted_date' => date('Y-m-d H:i:s', filemtime($file)),
                    ];
                }
            }
            
            // Sort by newest first
            usort($backups, function($a, $b) {
                return $b->created_at - $a->created_at;
            });
        }
        
        // Get total size of all backups
        $totalSize = array_sum(array_column($backups, 'size'));
        
        return view('backup.index', compact('backups', 'totalSize'));
    }

    /**
     * Create a new backup
     */
    public function create(Request $request)
    {
        set_time_limit(300); // 5 minutes timeout
        
        try {
            $filename = 'backup_' . Carbon::now()->format('Y-m-d_H-i-s') . '.sql';
            $path = storage_path('app/backups/' . $filename);
            
            // Create directory if doesn't exist
            if (!is_dir(storage_path('app/backups'))) {
                mkdir(storage_path('app/backups'), 0755, true);
            }
            
            // Run backup command
            $this->runBackup($path);
            
            if (!file_exists($path) || filesize($path) === 0) {
                return redirect()->route('backup.index')
                    ->with('error', 'Backup failed. Please try again.');
            }
            
            return redirect()->route('backup.index')
                ->with('success', 'Backup created successfully: ' . $filename);
            
        } catch (\Exception $e) {
            return redirect()->route('backup.index')
                ->with('error', 'Backup failed: ' . $e->getMessage());
        }
    }

    /**
     * Download a backup file
     */
    public function download($filename)
    {
        $path = storage_path('app/backups/' . $filename);
        
        if (!file_exists($path)) {
            return redirect()->route('backup.index')
                ->with('error', 'Backup file not found.');
        }
        
        return response()->download($path, $filename);
    }

    /**
     * Restore a backup file
     */
    public function restore(Request $request)
    {
        $request->validate([
            'filename' => 'required|string',
        ]);
        
        $filename = $request->filename;
        $path = storage_path('app/backups/' . $filename);
        
        if (!file_exists($path)) {
            return redirect()->route('backup.index')
                ->with('error', 'Backup file not found.');
        }
        
        // Read the backup file
        $sql = file_get_contents($path);
        
        if (empty($sql)) {
            return redirect()->route('backup.index')
                ->with('error', 'Backup file is empty.');
        }
        
        try {
            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            
            // Execute SQL statements
            $statements = $this->splitSql($sql);
            $count = 0;
            
            foreach ($statements as $statement) {
                if (trim($statement) !== '') {
                    DB::statement($statement);
                    $count++;
                }
            }
            
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            return redirect()->route('backup.index')
                ->with('success', "Database restored successfully from: {$filename}. ({$count} statements executed)");
            
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            return redirect()->route('backup.index')
                ->with('error', 'Restore failed: ' . $e->getMessage());
        }
    }

    /**
     * Delete a backup file
     */
    public function destroy($filename)
    {
        $path = storage_path('app/backups/' . $filename);
        
        if (!file_exists($path)) {
            return redirect()->route('backup.index')
                ->with('error', 'Backup file not found.');
        }
        
        if (unlink($path)) {
            return redirect()->route('backup.index')
                ->with('success', 'Backup deleted: ' . $filename);
        }
        
        return redirect()->route('backup.index')
            ->with('error', 'Failed to delete backup.');
    }

    /**
     * Run the backup process
     */
    private function runBackup($path)
    {
        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        // Auto-detect Laragon MySQL path
        $mysqlBase = 'C:\laragon\bin\mysql\\';
        $mysqlVersion = $this->findLaragonMysqlVersion($mysqlBase);
        
        if (!$mysqlVersion) {
            throw new \Exception('Could not find MySQL in Laragon.');
        }

        $mysqldump = $mysqlBase . $mysqlVersion . '\bin\mysqldump.exe';
        
        if (!file_exists($mysqldump)) {
            throw new \Exception('Could not find mysqldump at: ' . $mysqldump);
        }

        $command = sprintf(
            '"%s" --host=%s --user=%s --password=%s %s > "%s" 2>&1',
            $mysqldump,
            $host,
            $username,
            $password,
            $database,
            $path
        );

        exec($command, $output, $returnCode);

        if ($returnCode !== 0) {
            throw new \Exception('Backup failed with code: ' . $returnCode);
        }
    }

    /**
     * Find Laragon MySQL version
     */
    private function findLaragonMysqlVersion($basePath)
    {
        if (!is_dir($basePath)) {
            return null;
        }

        $versions = scandir($basePath);
        $mysqlDirs = array_filter($versions, function($dir) use ($basePath) {
            return strpos($dir, 'mysql-') === 0 && is_dir($basePath . $dir);
        });

        rsort($mysqlDirs);
        return $mysqlDirs[0] ?? null;
    }

    /**
     * Split SQL into statements
     */
    private function splitSql($sql)
    {
        $statements = [];
        $current = '';
        
        $lines = explode("\n", $sql);
        
        foreach ($lines as $line) {
            $line = trim($line);
            if (strpos($line, '--') === 0 || strpos($line, '/*') === 0 || empty($line)) {
                continue;
            }
            
            $current .= $line . "\n";
            if (substr(rtrim($line), -1) === ';') {
                $statements[] = $current;
                $current = '';
            }
        }
        
        if (!empty($current)) {
            $statements[] = $current;
        }
        
        return $statements;
    }
}