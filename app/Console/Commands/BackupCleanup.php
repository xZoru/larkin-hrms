<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class BackupCleanup extends Command
{
    protected $signature = 'backup:cleanup {--keep=30 : Number of backups to keep}';
    protected $description = 'Delete old backups';

    public function handle()
    {
        $keep = (int) $this->option('keep');
        $backupDir = storage_path('app/backups');
        
        if (!is_dir($backupDir)) {
            $this->error('Backup directory not found.');
            return;
        }

        $files = glob($backupDir . '/*.sql');
        
        if (count($files) <= $keep) {
            $this->info('No cleanup needed. (' . count($files) . ' files, keeping ' . $keep . ')');
            return;
        }

        // Sort by modification time (oldest first)
        usort($files, function($a, $b) {
            return filemtime($a) - filemtime($b);
        });

        $toDelete = array_slice($files, 0, count($files) - $keep);
        $deleted = 0;

        foreach ($toDelete as $file) {
            if (unlink($file)) {
                $deleted++;
                $this->info('Deleted: ' . basename($file));
            }
        }

        $this->info('Deleted ' . $deleted . ' old backup(s). Kept ' . $keep . ' most recent.');
    }
}