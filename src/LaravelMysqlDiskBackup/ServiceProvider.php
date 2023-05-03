<?php

namespace LaravelMysqlDiskBackup;

use Illuminate\Support\ServiceProvider as BaseServiceProvider;

class ServiceProvider extends BaseServiceProvider
{
    public function boot()
    {
        $this->publishes([
            __DIR__.'/config/config.php' => config_path('laravel-mysql-disk-backup.php'),
        ], 'config');
    }

    public function register()
    {
        $this->commands([
            MySqlDiskBackup::class,
        ]);
    }
}
