<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class StorageLinkCommand extends Command
{
    protected $signature = 'storage:link-custom';
    protected $description = 'Create the storage symlink for KidLink';

    public function handle()
    {
        $target = storage_path('app/public');
        $link = public_path('storage');

        if (file_exists($link)) {
            $this->error('The "public/storage" link already exists.');
            return 1;
        }

        $this->laravel->make('files')->link($target, $link);
        $this->info('The [public/storage] link has been connected.');
        return 0;
    }
}
