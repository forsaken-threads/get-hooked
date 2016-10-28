<?php namespace ForsakenThreads\GetHooked\Tests;

class TestTest extends BaseTest  {

    public function startUp()
    {
    }

    public function testing123()
    {
        /** @var Handler $handler */
        $this->client->get('/index.php')
            ->saveResponseHandler($handler);
        var_dump($handler->getRawResponse());
    }
}