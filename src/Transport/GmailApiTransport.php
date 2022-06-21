<?php

	namespace GoogleApiMail\Transport;

	use google\Client;
	use Google\Exception;
	use Google\Service\Gmail;

	class GmailApiTransport
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
			$this->client->addScope(Gmail::MAIL_GOOGLE_COM);
		}

		private function setCredentials($apifile)
		{
			$this->_credentials = $apifile;
		}
	}
