<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Carbon\Carbon;

class BackupDatabase extends Command
{
    protected $signature = 'backup:database {--filename= : Custom filename}';
    protected $description = 'Create a database backup';

    public function handle()
    {
        $this->info('Starting database backup...');

        // Create backup directory
        if (!is_dir(storage_path('app/backups'))) {
            mkdir(storage_path('app/backups'), 0755, true);
        }

        $filename = $this->option('filename') ?? 'backup_' . Carbon::now()->format('Y-m-d_H-i-s') . '.sql';
        $path = storage_path('app/backups/' . $filename);

        $database = config('database.connections.mysql.database');
        $username = config('database.connections.mysql.username');
        $password = config('database.connections.mysql.password');
        $host = config('database.connections.mysql.host');

        // ✅ LARAGON MYSQL PATH - AUTOMATICALLY DETECTS YOUR VERSION
        $mysqlBase = 'C:\laragon\bin\mysql\\';
        $mysqlVersion = $this->findLaragonMysqlVersion($mysqlBase);
        
        if (!$mysqlVersion) {
            $this->error('❌ Could not find MySQL in Laragon.');
            $this->error('Please check: ' . $mysqlBase);
            return;
        }

        $mysqldump = $mysqlBase . $mysqlVersion . '\bin\mysqldump.exe';
        
        if (!file_exists($mysqldump)) {
            $this->error('❌ Could not find mysqldump at: ' . $mysqldump);
            $this->error('Please verify your Laragon MySQL installation.');
            return;
        }

        $this->info('📂 Using MySQL: ' . $mysqlVersion);

        // Build the command with full paths
        $command = sprintf(
            '"%s" --host=%s --user=%s --password=%s %s > "%s" 2>&1',
            $mysqldump,
            $host,
            $username,
            $password,
            $database,
            $path
        );

        $this->info('🔄 Dumping database: ' . $database);
        exec($command, $output, $returnCode);

        if ($returnCode === 0 && file_exists($path) && filesize($path) > 0) {
            $this->info('✅ Backup created successfully!');
            $this->info('📁 File: ' . $filename);
            $this->info('📊 Size: ' . number_format(filesize($path) / 1024, 2) . ' KB');
            $this->info('📂 Location: ' . $path);
        } else {
            $this->error('❌ Backup failed.');
            $this->error('Return code: ' . $returnCode);
            if (!empty($output)) {
                $this->error('Output: ' . implode("\n", $output));
            }
        }
    }

    /**
     * Automatically find the Laragon MySQL version
     */
    private function findLaragonMysqlVersion($basePath)
    {
        if (!is_dir($basePath)) {
            return null;
        }

        $versions = scandir($basePath);
        $mysqlDirs = array_filter($versions, function($dir) {
            return strpos($dir, 'mysql-') === 0 && is_dir('C:\laragon\bin\mysql\\' . $dir);
        });

        // Sort versions (newest first)
        rsort($mysqlDirs);

        return $mysqlDirs[0] ?? null;
    }
}