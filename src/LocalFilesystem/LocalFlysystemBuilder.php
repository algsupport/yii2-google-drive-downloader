<?php

namespace GoogleDriveDownloader\LocalFilesystem;

use Yii;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\Filesystem;

class LocalFlysystemBuilder
{
    public $path;

    public function build(): Filesystem
    {
        $adapter = new LocalFilesystemAdapter(Yii::getAlias($this->path));
        return new Filesystem($adapter);
    }
}
