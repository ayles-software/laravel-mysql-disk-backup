<?php

namespace LaravelMysqlS3Backup\Actions;

use RuntimeException;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Process;

class MySqlDatabaseDumper
{
    public string $fileName = '';

    public array $debugMessages = [];

    public function dump()
    {
        $cmd = sprintf('mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers',
            escapeshellarg(config('database.connections.mysql.host')),
            escapeshellarg(config('database.connections.mysql.port')),
            escapeshellarg(config('database.connections.mysql.username')),
            escapeshellarg(config('database.connections.mysql.password'))
        );

        if (config('laravel-mysql-s3-backup.custom_mysqldump_args')) {
            $cmd .= ' '.config('laravel-mysql-s3-backup.custom_mysqldump_args');
        }

        $cmd .= ' '.escapeshellarg(config('database.connections.mysql.database'));

        $this->fileName = sprintf('/tmp/%s-%s-mysql-backup.sql',
            Str::slug(config('app.name')),
            date('Ymd-His')
        );

        // Handle gzip
        if (config('laravel-mysql-s3-backup.gzip')) {
            $this->fileName .= '.gz';
            $cmd .= sprintf(' | gzip > %s', escapeshellarg($this->fileName));
        } else {
            $cmd .= sprintf(' > %s', escapeshellarg($this->fileName));
        }

        $this->debugMessages[] = 'Running backup for database `'.config('database.connections.mysql.database').'`';
        $this->debugMessages[] = "Running command: {$cmd}";

        $process = Process::fromShellCommandline($cmd)
            ->setTimeout(config('laravel-mysql-s3-backup.sql_timout'))
            ->run();

        if (! $process->isSuccessful()) {
            throw new RuntimeException(sprintf(
                'Unable to dump database for %s with a file name of %. Error: %s',
                now()->toDateString(),
                $this->fileName,
                $process->getErrorOutput()
            ));
        }

        $this->debugMessages[] = "Backup saved to {$this->fileName}";
    }

    public function deleteLocalFile()
    {
        unlink($this->fileName);
    }
}
