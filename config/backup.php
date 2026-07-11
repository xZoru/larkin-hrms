<?php

return [
    'backup' => [
        'name' => env('APP_NAME', 'hrms-backup'),
        
        'source' => [
            'files' => [
                'include' => [
                    base_path('public/uploads'),
                    base_path('storage/app/public'),
                ],
                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                    base_path('storage/logs'),
                    base_path('storage/backup-temp'),
                ],
                'follow_links' => false,
                'ignore_unreadable_directories' => false,
                'relative_path' => null,
            ],
            
            'databases' => [
                'mysql',
            ],
        ],
        
        'destination' => [
            'filename_prefix' => 'hrms_',
            'disks' => [
                'local',
            ],
        ],
        
        'temporary_directory' => storage_path('app/backup-temp'),
    ],
    
    'notifications' => [
        'notifications' => [
            \Spatie\Backup\Notifications\Notifications\BackupHasFailed::class => [],
            \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFound::class => [],
            \Spatie\Backup\Notifications\Notifications\CleanupHasFailed::class => [],
            \Spatie\Backup\Notifications\Notifications\BackupWasSuccessful::class => [],
            \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFound::class => [],
            \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessful::class => [],
        ],
        
        'mail' => [
            'to' => env('BACKUP_EMAIL', 'admin@yourcompany.com'),
        ],
    ],
    
    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,
        'default_strategy' => [
            'keep_all_backups_for_days' => 7,
            'keep_daily_backups_for_days' => 16,
            'keep_weekly_backups_for_weeks' => 8,
            'keep_monthly_backups_for_months' => 4,
            'keep_yearly_backups_for_years' => 2,
            'delete_oldest_backups_when_using_more_megabytes_than' => 5000,
        ],
    ],
];