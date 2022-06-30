<?php

namespace GoogleDriveDownloader\LocalFilesystem;

use Yii;
use League\Flysystem\Local\LocalFilesystemAdapter;
use League\Flysystem\Filesystem;

class LocalFlysystemBuilder
{
    private string $_path = "";

    public Filesystem $filesystem;

    public function __construct($path)
    {
        $this->setPath($path);
        $adapter = new LocalFilesystemAdapter(Yii::getAlias($this->_path));
        $this->filesystem = new Filesystem($adapter);
    }

    private function setPath($path)
    {
        $this->_path = $path;
    }
}
