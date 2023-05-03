# Laravel MySQL to Laravel Disk Backup

This is a very simple database backup script for Laravel. It takes a `mysqldump` 
and saves it to any Laravel disk. It also supports trimming backups to only have X days worth.

## Installation

Install package

 ```
 composer require ayles-software/laravel-mysql-disk-backup
 ```

Publish and edit the config

 ```bash
 php artisan vendor:publish --provider="LaravelMysqlDiskBackup\ServiceProvider"
 ```

Edit config `config/laravel-mysql-disk-backup.php` and add disks to send the MySql backup to:

 ```php
 'disks' => [
     's3' => [
         'folder' => env('S3_BACKUP_FOLDER'),
     ],

      'r2' => [
          'folder' => env('R2_BACKUP_FOLDER'),
      ],

      'ftp' => [
          'folder' => env('FTP_BACKUP_FOLDER'),
      ],
 ],
 ```

## Usage

```bash
$ php artisan db:mysql-disk-backup
```

That's it. No arguments or optional parameters.

## License

Laravel MySQL to Laravel Disk Backup is open-sourced software licensed under the [MIT license](https://github.com/ayles-software/laravel-mysql-disk-backup/blob/master/LICENSE.md).
