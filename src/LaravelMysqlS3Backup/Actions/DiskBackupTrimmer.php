<?php

namespace LaravelMysqlS3Backup\Actions;

use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class DiskBackupTrimmer
{
    public $disk;
    public $when;

    private function __construct($disk, public ?string $folder = null)
    {
        $this->disk = Storage::disk($disk);
        $this->when = Carbon::now()->subDays(config('laravel-mysql-s3-backup.rolling_backup_days'))->startOfDay();
    }

    public static function make($disk, $folder)
    {
        return new static($disk, $folder);
    }

    public function run()
    {
        collect($this->disk->files($this->folder))
            ->filter(function ($filename) {
                $parts = explode('-', $filename);
                $date = $parts[count($parts) - 3];

                return (Carbon::createFromFormat('Ymd', $date))->lt($this->when);
            })
            ->tap(function ($filenames) {
                $this->disk->delete($filenames);
            });
    }
}
