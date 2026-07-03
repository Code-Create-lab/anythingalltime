<?php

namespace App\Traits;

use DB;
use Illuminate\Support\Facades\Storage;

trait ImageStoragePicker
{
    protected string $storage_space;

    public function getImageStorage()
    {
        $storage = DB::table('image_space')->first();

        if ($storage->aws == 1) {
            $this->storage_space = 's3.aws';
        } elseif ($storage->digital_ocean == 1) {
            $this->storage_space = 's3.digitalocean';
        } else {
            $this->storage_space = 'same_server';
        }

        $bucket = config("filesystems.disks.{$this->storage_space}.bucket");

        if ($this->storage_space != 'same_server' && !empty($bucket)) {
            // S3/AWS SDK rejects an empty object key, so resolve the URL with a
            // placeholder key and strip it back off to get the bucket base URL.
            $placeholder = 'placeholder';
            $full = Storage::disk($this->storage_space)->url($placeholder);
            $url_aws = rtrim(substr($full, 0, -strlen($placeholder)), '/');
        } else {
            // No cloud bucket configured -> fall back to local app URL.
            $url_aws = url('/');
        }

        return $url_aws;
    }
}
