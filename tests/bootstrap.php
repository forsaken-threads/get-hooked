<?php namespace ForsakenThreads\GetHooked\Tests;

use ForsakenThreads\Diplomatic\Client;
use ForsakenThreads\Diplomatic\ResponseHandler;
use ForsakenThreads\Diplomatic\SelfHandling;
use PHPUnit\Framework\TestCase;

class Handler extends ResponseHandler {

    function wasErrored()
    {
        return $this->filteredResponse == 'Error';
    }

    function wasFailed()
    {
        return $this->filteredResponse == 'Failed';
    }

    function wasSuccessful()
    {
        return !$this->wasErrored() && !$this->wasFailed();
    }
}

class SelfHandler extends Handler implements SelfHandling {

    function onError()
    {
        return 'WasErrored';
    }

    function onFailure()
    {
        return 'WasFailed';
    }

    function onSuccess()
    {
        return 'WasSuccessful';
    }
}

class BaseTest extends TestCase {

    protected $client;

    public function __construct()
    {
        $this->client = new Client('http://localhost:8888', new Handler());
        $this->client->addHeaders([
            'X-Gitlab-Event' => 'Push Hook',
            'X-Gitlab-Token' => 'ABC123',
        ]);
        parent::__construct();
    }
}

