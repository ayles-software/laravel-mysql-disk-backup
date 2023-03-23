<?php

use Illuminate\Support\Str;

return [
    'disks' => [
        's3' => [
            'folder' => env('S3_BACKUP_FOLDER'),
        ],
    ],

    /*
     * Want to add some custom mysqldump args?
     */
    'custom_mysqldump_args' => null,

    /*
     * Whether to gzip the .sql file
     */
    'gzip' => true,

    /*
     * Time allowed to run backup
     */
    'sql_timout' => 7200, // 2 hours

    /*
     * Backup filename
     */
    'filename_prefix' => Str::slug(env('APP_NAME')).'backup',

    /*
     * Where to store the backup file locally
     */
    'backup_dir' => '/tmp',

    /*
     * Do you want to keep a rolling number of
     * backups on S3? How many days worth?
     */
    'rolling_backup_days' => 14,
];
