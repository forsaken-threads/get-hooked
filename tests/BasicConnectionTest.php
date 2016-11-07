<?php namespace ForsakenThreads\GetHooked\Tests;

use Flintstone\Flintstone;
use ForsakenThreads\Diplomatic\Client;
use PHPUnit\Framework\TestCase;

class BasicConnectionTest extends TestCase {

    /** @var Client */
    protected $client;

    public function setUp()
    {
        $this->client = new Client('http://localhost:8888', new Handler());
    }

    public function tearDown()
    {
        parent::tearDown();
        foreach (glob(__DIR__ . '/test-storage/*.dat') as $store) {
            unlink($store);
            $store = substr(strrchr($store, '/'), 1, -4);
            Flintstone::unload($store);
        }
        clearstatcache();
    }

    public function testUnauthorizedRequest()
    {
        /** @var Handler $handler */
        $this->client->setHeaders([])
            ->get('/basic-connection.php')
            ->saveResponseHandler($handler);
        $this->assertEquals(401, $handler->getCode());
    }

    public function testForbiddenRequest()
    {
        /** @var Handler $handler */
        $this->client->setHeaders(['X-GitLab-Token' => 'ABC123'])
            ->get('/basic-connection.php')
            ->saveResponseHandler($handler);
        $this->assertEquals(403, $handler->getCode());
    }

    public function testBadRequest()
    {
        /** @var Handler $handler */
        $this->client->setHeaders([
                'X-GitLab-Token' => 'ABC123',
                'Content-Type' => 'application/json',
                'X-GitLab-Event' => 'Test Hook'
            ])
            ->get('/basic-connection.php', 'Not JSON')
            ->saveResponseHandler($handler);
        $this->assertEquals(400, $handler->getCode());
    }

    public function testUnprocessableRequest()
    {
        /** @var Handler $handler */
        $this->client->setHeaders([
                'X-GitLab-Token' => 'ABC123',
                'Content-Type' => 'application/json',
                'X-GitLab-Event' => 'Test Hook'
            ])
            ->get('/basic-connection.php', '{"no_object_kind":"test"}')
            ->saveResponseHandler($handler);
        $this->assertEquals(422, $handler->getCode());
    }

    public function testValidRequest()
    {
        /** @var Handler $handler */
        $this->client->setHeaders([
                'X-GitLab-Token' => 'ABC123',
                'Content-Type' => 'application/json',
                'X-GitLab-Event' => 'Test Hook'
            ])
            ->get('/basic-connection.php', '{"object_kind":"test"}')
            ->saveResponseHandler($handler);
        $this->assertEquals(200, $handler->getCode());
    }
}