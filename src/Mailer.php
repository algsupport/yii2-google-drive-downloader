<?php
/**
 * @link http://www.algsupport.com/
 * @copyright Copyright (c) 2021 ALGSUPPORT OÜ
 */


namespace GoogleApiMail;

use Yii;
use yii\mail\BaseMailer;
use Google\Service\Gmail;
use yii\base\InvalidConfigException;


class Mailer extends BaseMailer
{
    private ?Gmail $_gmailMailer;

    private $_transport = [];

	public $messageClass = Message::class;

	/**
	 * @throws InvalidConfigException
	 */
	public function getGmailMailer(): Gmail
    {
        if (!isset($this->_gmailMailer) or !is_object($this->_gmailMailer)) {
            $this->_gmailMailer = $this->createGmailMailer();
        }

        return $this->_gmailMailer;
    }

	/**
	 * @throws InvalidConfigException
	 */
	protected function createGmailMailer(): Gmail
    {
        return new Gmail($this->getTransport());
    }

	/**
	 * @throws InvalidConfigException
	 */
	public function setTransport($transport)
    {
        if (!is_array($transport) && !is_object($transport)) {
            throw new InvalidConfigException('"' . get_class($this) . '::transport" should be either object or array, "' . gettype($transport) . '" given.');
        }
        $this->_transport = $transport;
		$this->_gmailMailer = null;
    }
	/**
	 * @throws InvalidConfigException
	 */
	public function getTransport()
	{
        if (!is_object($this->_transport)) {
            $this->_transport = $this->createTransport($this->_transport);
        }

        return $this->_transport;
    }

	/**
	 * @throws InvalidConfigException
	 */
	protected function createTransport(array $config)
    {
        if (!isset($config['class'])) {
            $config['class'] = Transport\GmailApiTransport::class;
        }
	    return $this->createGmailObject($config)->client;
    }

	/**
	 * @throws InvalidConfigException
	 */
	protected function createGmailObject(array $config)
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


	/**
	 * @throws InvalidConfigException
	 */
	protected function sendMessage($message): Gmail\Message
	{
        $address = $message->getTo();

		$this->getGmailMailer()->getClient()->setSubject($message->getFrom());

        if (is_array($address)) {
            $address = implode(', ', array_keys($address));
        }
        Yii::info('Sending email "' . $message->getSubject() . '" to "' . $address . '"', __METHOD__);
		$googleMessage = Yii::createObject(Gmail\Message::class);
		$googleMessage->setRaw(strtr(base64_encode($message->toString()), array('+' => '-', '/' => '_')));
        return $this->getGmailMailer()->users_messages->send('me', $googleMessage);
    }

}
