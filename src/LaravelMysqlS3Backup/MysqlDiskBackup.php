<?php

namespace LaravelMysqlS3Backup;

use Aws\S3\S3Client;
use Aws\S3\MultipartUploader;
use Illuminate\Console\Command;
use Aws\Exception\MultipartUploadException;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Stringable;

class MysqlDiskBackup extends Command
{
    protected $name = 'db:backup';

    protected $description = 'Create a sqldump of your MySQL database and upload it to any laravel disks';

    protected string $fileName = '';

    public function handle()
    {
        $this->dumpDatabase();

        foreach (config('laravel-mysql-s3-backup.disks') as $diskName => $item) {
            $disk = Storage::disk($diskName);

            $putPath = basename($this->fileName);

            if (isset($item['folder']) && $item['folder']) {
                $putPath = "{$item['folder']}/$putPath";
            }

            if ($this->output->isVerbose()) {
                $this->output->writeln(sprintf('Uploading %s to %s', $putPath, $diskName));
            }

            $disk->put($putPath, $this->fileName);

            if ($this->output->isVerbose()) {
                $this->output->writeln("Backup of {$putPath} successfully uploaded to {$diskName}");
            }

//            if (config('laravel-mysql-s3-backup.rolling_backup_days')) {
//                if ($this->output->isVerbose()) {
//                    $this->output->writeln("Trimming {$diskName} have have only ".config('laravel-mysql-s3-backup.rolling_backup_days').' days of backups');
//                }
//
//                S3BackupTrimmer::make(config('laravel-mysql-s3-backup.rolling_backup_days'), $diskName)->run();
//            }
        }

//        // Upload to S3
//        $s3 = new S3Client([
//            'credentials' => [
//                'key' => config('laravel-mysql-s3-backup.s3.key'),
//                'secret' => config('laravel-mysql-s3-backup.s3.secret'),
//            ],
//            'endpoint' => config('laravel-mysql-s3-backup.s3.endpoint'),
//            'region' => config('laravel-mysql-s3-backup.s3.region'),
//            'version' => 'latest',
//            'use_path_style_endpoint' => config('laravel-mysql-s3-backup.s3.use_path_style_endpoint'),
//        ]);
//
//        $bucket = config('laravel-mysql-s3-backup.s3.bucket');
//        $key = basename($fileName);
//
//        if ($folder = config('laravel-mysql-s3-backup.s3.folder')) {
//            $key = $folder . '/' . $key;
//        }
//
//
//
//        $uploader = new MultipartUploader($s3, $fileName, [
//            'bucket' => $bucket,
//            'key' => $key,
//        ]);
//
//        try {
//            $uploader->upload();
//        } catch (MultipartUploadException $e) {
//            if ($this->output->isVerbose()) {
//                $this->output->writeln(sprintf(
//                    'Unable to upload "%s" backup to s3. Error: %s',
//                    $fileName,
//                    $e->getMessage()
//                ));
//            }
//        }

        // Delete the local tmp file
        if ($this->output->isVerbose()) {
            $this->output->writeln("Deleting local backup file {$this->fileName}");
        }

        unlink($this->fileName);
    }

    protected function dumpDatabase()
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

        $this->fileName = sprintf('%s/%s-%s.sql',
            config('laravel-mysql-s3-backup.backup_dir'),
            config('laravel-mysql-s3-backup.filename_prefix'),
            date('Ymd-His')
        );

        // Handle gzip
        if (config('laravel-mysql-s3-backup.gzip')) {
            $this->fileName .= '.gz';
            $cmd .= sprintf(' | gzip > %s', escapeshellarg($this->fileName));
        } else {
            $cmd .= sprintf(' > %s', escapeshellarg($this->fileName));
        }

        if ($this->output->isVerbose()) {
            $this->output->writeln('Running backup for database `'.config('database.connections.mysql.database').'`');
            $this->output->writeln('Saving to '.$this->fileName);
        }

        if ($this->output->isDebug()) {
            $this->output->writeln("Running command: {$cmd}");
        }

        $process = Process::fromShellCommandline($cmd);
        $process->setTimeout(config('laravel-mysql-s3-backup.sql_timout'));
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error($process->getErrorOutput());

            if ($this->output->isVerbose()) {
                $this->output->writeln(sprintf(
                    'Unable to dump database for %s with a file name of %. Error: %s',
                    now()->toDateString(),
                    $this->fileName,
                    $process->getErrorOutput()
                ));
            }

            return;
        }

        if ($this->output->isVerbose()) {
            $this->output->writeln("Backup saved to {$this->fileName}");
        }
    }
}
