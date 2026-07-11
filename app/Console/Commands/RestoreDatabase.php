<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class RestoreDatabase extends Command
{
    protected $signature = 'backup:restore {filename? : The backup file to restore (optional - shows list if not provided)} 
                            {--latest : Restore the latest backup}';
    protected $description = 'Restore a database backup';

    public function handle()
    {
        $backupDir = storage_path('app/backups');
        
        if (!is_dir($backupDir)) {
            $this->error('❌ No backups found. Directory does not exist.');
            return;
        }

        $files = glob($backupDir . '/*.sql');
        $files = array_filter($files, function($file) {
            return is_file($file);
        });

        if (empty($files)) {
            $this->error('❌ No backup files found in: ' . $backupDir);
            return;
        }

        // Sort by creation time (newest first)
        usort($files, function($a, $b) {
            return filemtime($b) - filemtime($a);
        });

        $selectedFile = null;

        if ($this->option('latest')) {
            $selectedFile = $files[0];
            $this->info('📁 Using latest backup: ' . basename($selectedFile));
        } elseif ($this->argument('filename')) {
            $filename = $this->argument('filename');
            $fullPath = $backupDir . '/' . $filename;
            if (file_exists($fullPath)) {
                $selectedFile = $fullPath;
            } else {
                $this->error('❌ File not found: ' . $filename);
                return;
            }
        } else {
            // Show list and let user choose
            $this->info('📂 Available backups:');
            $choices = [];
            foreach ($files as $index => $file) {
                $size = number_format(filesize($file) / 1024, 2);
                $date = date('Y-m-d H:i:s', filemtime($file));
                $choices[] = basename($file) . " ({$size} KB - {$date})";
            }

            $choice = $this->choice('Select backup to restore:', $choices);
            
            // Find the selected file
            $selectedIndex = array_search($choice, $choices);
            if ($selectedIndex !== false) {
                $selectedFile = $files[$selectedIndex];
            }
        }

        if (!$selectedFile) {
            $this->error('❌ No backup selected.');
            return;
        }

        $this->warn('⚠️  WARNING: This will overwrite the entire database!');
        $this->warn('⚠️  This action is NOT reversible!');
        
        if (!$this->confirm('Are you sure you want to restore this backup?', false)) {
            $this->info('✅ Restore cancelled.');
            return;
        }

        if (!$this->confirm('FINAL CONFIRMATION: Restore database from: ' . basename($selectedFile) . '?', false)) {
            $this->info('✅ Restore cancelled.');
            return;
        }

        $this->info('🔄 Starting database restore...');
        
        // Read the backup file
        $sql = file_get_contents($selectedFile);
        
        if (empty($sql)) {
            $this->error('❌ Backup file is empty.');
            return;
        }

        $this->info('📊 Backup size: ' . number_format(strlen($sql) / 1024, 2) . ' KB');

        try {
            // Disable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=0');
            
            // Split SQL into individual statements
            $statements = $this->splitSql($sql);
            $total = count($statements);
            $current = 0;
            
            $this->info('🔄 Executing ' . $total . ' SQL statements...');
            
            $bar = $this->output->createProgressBar($total);
            
            foreach ($statements as $statement) {
                if (trim($statement) !== '') {
                    DB::statement($statement);
                }
                $bar->advance();
                $current++;
            }
            
            $bar->finish();
            $this->newLine();
            
            // Re-enable foreign key checks
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            
            $this->info('✅ Database restored successfully!');
            $this->info('📁 Restored from: ' . basename($selectedFile));
            $this->info('📊 Total statements executed: ' . $total);
            
        } catch (\Exception $e) {
            DB::statement('SET FOREIGN_KEY_CHECKS=1');
            $this->error('❌ Restore failed: ' . $e->getMessage());
        }
    }

    /**
     * Split SQL file into individual statements
     */
    private function splitSql($sql)
    {
        $statements = [];
        $current = '';
        
        $lines = explode("\n", $sql);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Skip comments
            if (strpos($line, '--') === 0 || strpos($line, '/*') === 0) {
                continue;
            }
            
            if (empty($line)) {
                continue;
            }
            
            $current .= $line . "\n";
            
            // Check if this line ends with a semicolon
            if (substr(rtrim($line), -1) === ';') {
                $statements[] = $current;
                $current = '';
            }
        }
        
        // Add any remaining statements
        if (!empty($current)) {
            $statements[] = $current;
        }
        
        return $statements;
    }
}