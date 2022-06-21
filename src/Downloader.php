<?php
/**
 * @link http://www.algsupport.com/
 * @copyright Copyright (c) 2021 ALGSUPPORT OÜ
 */


namespace GoogleDriveDownloader;

use Yii;
use Google\Service\Drive;
use yii\base\InvalidConfigException;


class Downloader extends Component
{


    private $_client = [];

	public function setClient($client)
    {
        if (!is_array($client) && !is_object($client)) {
            throw new InvalidConfigException('"' . get_class($this) . '::client" should be either object or array, "' . gettype($client) . '" given.');
        }
        $this->_client = $client;
		$this->_gmailMailer = null;
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

}
