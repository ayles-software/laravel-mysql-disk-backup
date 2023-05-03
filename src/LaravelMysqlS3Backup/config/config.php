<?php

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
     * Want to keep X number of days worth of backups?
     */
    'rolling_backup_days' => 14,
];
