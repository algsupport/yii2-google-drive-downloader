<?php
/**
 * @link http://www.algsupport.com/
 * @copyright Copyright (c) 2021 ALGSUPPORT OÜ
 */


namespace GoogleDriveDownloader;

use Yii;
use Google\Service\Drive;
use yii\base\Component;
use yii\base\InvalidConfigException;
use yii\helpers\FileHelper;


class Downloader extends Component
{
    private $_client = [];
    private $_driveDownloader;
    private $_filesystem = [];

    public function getDriveDownloader(): Drive
    {
        if (!isset($this->_driveDownloader) or !is_object($this->_driveDownloader)) {
            $this->_driveDownloader = $this->createDriveDownloader();
        }

        return $this->_driveDownloader;
    }

    protected function createDriveDownloader(): Drive
    {
        return new Drive($this->getClient());
    }

    public function setFilesystem($filesystem)
    {
        if (!is_array($filesystem) && !is_object($filesystem)) {
            throw new InvalidConfigException('"' . get_class($this) . '::filesystem" should be either object or array, "' . gettype($filesystem) . '" given.');
        }
        $this->_filesystem = $filesystem;
    }

    public function getFilesystem()
    {
        if (!is_object($this->_filesystem)) {
            $this->_filesystem = $this->createFilesystem($this->_filesystem);
        }

        return $this->_filesystem;
    }

    protected function createFilesystem(array $config)
    {
        if (!isset($config['class'])) {
            $config['class'] = LocalFilesystem\LocalFlysystemBuilder::class;
        }
        return $this->createFilesystemObject($config)->filesystem;
    }

    public function setClient($client)
    {
        if (!is_array($client) && !is_object($client)) {
            throw new InvalidConfigException('"' . get_class($this) . '::client" should be either object or array, "' . gettype($client) . '" given.');
        }
        $this->_client = $client;
    }

    public function getClient()
    {
        if (!is_object($this->_client)) {
            $this->_client = $this->createClient($this->_client);
        }

        return $this->_client;
    }

    protected function createClient(array $config)
    {
        if (!isset($config['class'])) {
            $config['class'] = Client\DownloaderClient::class;
        }
        return $this->createDriveObject($config)->client;
    }

    protected function createDriveObject(array $config)
    {
        if (isset($config['class'])) {
            $className = $config['class'];
            unset($config['class']);
        } else {
            throw new InvalidConfigException('Object configuration must be an array containing a "class" element.');
        }

        if (isset($config['credentials'])) {
            $object = Yii::createObject($className, [$config['credentials']]);
            unset($config['credentials']);
        } else {
            throw new InvalidConfigException('Object configuration must be an array containing a "credentials" element.');
        }

        return $object;
    }

    protected function createFilesystemObject(array $config)
    {
        if (isset($config['class'])) {
            $className = $config['class'];
            unset($config['class']);
        } else {
            throw new InvalidConfigException('Object configuration must be an array containing a "class" element.');
        }

        if (isset($config['path'])) {
            $object = Yii::createObject($className, [$config['path']]);
            unset($config['path']);
        } else {
            throw new InvalidConfigException('Object configuration must be an array containing a "path" element.');
        }

        return $object;
    }

    public function get_file_id_from_url($file_url)
    {
        if (str_contains($file_url, 'google.com')) {
            switch (true) {
                case (str_contains($file_url, 'spreadsheets/d/')):
                    $parts = explode('spreadsheets/d/', $file_url);
                    break;
                case (str_contains($file_url, 'file/d/')):
                    $parts = explode('file/d/', $file_url);
                    break;
                case (str_contains($file_url, 'open?id=')):
                    $parts = explode('open?id=', $file_url);
                    break;
                default:
                    return false;
            }
            if (str_contains($parts[1], '/')) {
                $parts = explode('/', $parts[1]);
                $file_id = $parts[0];
            } else {
                $file_id = $parts[1];
            }
            return $file_id;
        } else {
            return false;
        }
    }

    public function download($file_id, $path = null)
    {
        $current_file_info = $this->driveDownloader->files->get($file_id, ['supportsAllDrives' => true, 'fields' => 'mimeType, name, thumbnailLink, iconLink, exportLinks']);
        if ($current_file_info->exportLinks) {
            $current_file = $this->driveDownloader->files->export($file_id, 'application/pdf');
            $extensions = ['pdf'];

        } else {
            $current_file = $this->driveDownloader->files->get($file_id, ['supportsAllDrives' => true, 'alt' => 'media']);
            $extensions = FileHelper::getExtensionsByMimeType($current_file_info->mimeType);
        }
        $file_path = $path . $file_id . '.' . end($extensions);
        $this->filesystem->write($file_path, $current_file->getBody());
        return $file_path;
    }

}
