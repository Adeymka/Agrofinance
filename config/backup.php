<?php

return [

    'backup' => [

        'name'   => env('APP_NAME', 'AgroFinance+'),

        'source' => [
            'files' => [
                'include' => [
                    base_path(),
                ],
                'exclude' => [
                    base_path('vendor'),
                    base_path('node_modules'),
                    base_path('.git'),
                    storage_path('debugbar'),
                    storage_path('logs'),
                ],
                'follow_links'             => false,
                'ignore_unreadable_dirs'   => true,
                'relative_path'            => base_path(),
            ],

            'databases' => [
                'mysql',
            ],
        ],

        'database_dump_compressor'  => null,
        'database_dump_file_extension' => '',

        'destination' => [
            'filename_prefix' => 'backup_',
            'disks'           => [
                'local',         // sauvegarde locale
                // 's3',         // decommenter pour dupliquer vers S3 (#12)
            ],
        ],

        'temporary_directory' => storage_path('app/backup-temp'),

        'password' => env('BACKUP_ARCHIVE_PASSWORD', null),
        'encryption' => 'default',

        'source_path_prefix_to_remove' => base_path(),
    ],

    'notifications' => [
        'notifications' => [
            \Spatie\Backup\Notifications\Notifications\BackupHasFailed::class         => ['mail'],
            \Spatie\Backup\Notifications\Notifications\UnhealthyBackupWasFound::class  => ['mail'],
            \Spatie\Backup\Notifications\Notifications\CleanupHasFailed::class         => ['mail'],
            \Spatie\Backup\Notifications\Notifications\BackupWasSuccessful::class      => [],
            \Spatie\Backup\Notifications\Notifications\HealthyBackupWasFound::class    => [],
            \Spatie\Backup\Notifications\Notifications\CleanupWasSuccessful::class     => [],
        ],
        'notifiable' => \Spatie\Backup\Notifications\Notifiable::class,
        'mail' => [
            'to' => env('BACKUP_MAIL_TO', 'admin@agrofinanceplus.bj'),
        ],
        'slack' => [
            'webhook_url' => env('BACKUP_SLACK_WEBHOOK_URL', ''),
            'channel'     => null,
            'username'    => null,
            'icon'        => null,
        ],
        'discord' => [
            'webhook_url' => env('BACKUP_DISCORD_WEBHOOK_URL', ''),
            'username'    => null,
            'avatar_url'  => null,
        ],
    ],

    'monitor_backups' => [
        [
            'name'                              => env('APP_NAME', 'AgroFinance+'),
            'disks'                             => ['local'],
            'health_checks'                     => [
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumAgeInDays::class   => 1,
                \Spatie\Backup\Tasks\Monitor\HealthChecks\MaximumStorageInMegabytes::class => 5000,
            ],
        ],
    ],

    'cleanup' => [
        'strategy' => \Spatie\Backup\Tasks\Cleanup\Strategies\DefaultStrategy::class,
        'default_strategy' => [
            'keep_all_backups_for_days'            => 7,
            'keep_daily_backups_for_days'          => 16,
            'keep_weekly_backups_for_weeks'        => 8,
            'keep_monthly_backups_for_months'      => 4,
            'keep_yearly_backups_for_years'        => 2,
            'delete_oldest_backups_when_using_more_megabytes_of_storage' => 5000,
        ],
    ],
];
