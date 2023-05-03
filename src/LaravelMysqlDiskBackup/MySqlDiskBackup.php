<?php

namespace LaravelMysqlDiskBackup;

use Illuminate\Console\Command;
use LaravelMysqlDiskBackup\Actions\DiskUpload;
use LaravelMysqlDiskBackup\Actions\DiskBackupTrimmer;
use LaravelMysqlDiskBackup\Actions\MySqlDatabaseDumper;

class MySqlDiskBackup extends Command
{
    protected $name = 'db:mysql-disk-backup';

    protected $description = 'Create a SQL dump of your database and upload it to any Laravel disk';

    public function __construct(public MySqlDatabaseDumper $dumper)
    {
    }

    public function handle()
    {
        $this->dumper->dump();

        if ($this->output->isVerbose()) {
            foreach ($this->dumper->debugMessages as $message) {
                $this->output->writeln($message);
            }
        }

        foreach (config('laravel-mysql-s3-backup.disks') as $diskName => $diskConfig) {
            $diskUpload = DiskUpload::make($this->dumper->fileName)->run($diskName, $diskConfig);

            if ($this->output->isVerbose()) {
                foreach ($diskUpload->debugMessages as $message) {
                    $this->output->writeln($message);
                }
            }

            if (config('laravel-mysql-s3-backup.rolling_backup_days')) {
                if ($this->output->isVerbose()) {
                    $this->output->writeln("Trimming {$diskName} have have only ".config('laravel-mysql-s3-backup.rolling_backup_days').' days of backups.');
                }

                DiskBackupTrimmer::make($diskName, $diskConfig['folder'] ?? null)->run();
            }
        }

        // Delete the local tmp file
        if ($this->output->isVerbose()) {
            $this->output->writeln("Deleting local backup file {$this->dumper->fileName}");
        }

        $this->dumper->deleteLocalFile();
    }
}
