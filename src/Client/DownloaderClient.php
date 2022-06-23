<?php

namespace GoogleDriveDownloader\Client;

use Google\Client;
use Google\Service\Drive;

class DownloaderClient
{
    private string $_credentials = "";

    public Client $client;

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
