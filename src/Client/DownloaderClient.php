<?php

	namespace GoogleDriveDownloader\Client;

	use google\Client;
	use Google\Exception;
    use Google\Service\Drive;

	class DownloaderClient
	{
		private string $_credentials = "";

		public Client $client;

		/**
		 * @throws Exception
		 */
		public function __construct($apifile)
		{
			$this->setCredentials($apifile);
			$this->client = new Client();
			$this->client->setAuthConfig($this->_credentials);
			$this->client->addScope(Drive::DRIVE);
		}

		private function setCredentials($apifile)
		{
			$this->_credentials = $apifile;
		}
	}
