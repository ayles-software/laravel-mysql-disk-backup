<?php

namespace LaravelMysqlDiskBackup\Actions;

use Illuminate\Support\Facades\Storage;

class DiskUpload
{
    public array $debugMessages = [];

    public function __construct(public string $backupFilePath)
    {
    }

    public static function make($backupFilePath)
    {
        return new static($backupFilePath);
    }

    public function run($diskName, $config)
    {
        $disk = Storage::disk($diskName);

        $putPath = basename($this->backupFilePath);

        if (isset($config['folder']) && $config['folder']) {
            $putPath = "{$config['folder']}/$putPath";
        }

        $this->debugMessages[] = sprintf('Uploading %s to %s', $putPath, $diskName);

        $disk->put($putPath, $this->backupFilePath);

        $this->debugMessages[] = "Backup of {$putPath} successfully uploaded to {$diskName}";

        return $this;
    }
}
