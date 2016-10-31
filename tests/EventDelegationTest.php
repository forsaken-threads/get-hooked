<?php namespace ForsakenThreads\GetHooked\Tests;

class EventDelegationTest extends BaseTest {

    public function testOnAnyDelegation()
    {
        /** @var Handler $handler */
        $rand = rand(1000,9999);
        $json = ['object_kind' => 'onAny', 'rand' => $rand];
        $this->client->get('/event-delegation.php', json_encode($json))
            ->saveResponseHandler($handler);
        $this->assertEquals($json, $handler->getFilteredResponse());
    }

    public function testOnDelegation()
    {
        /** @var Handler $handler */
        $rand = rand(1000,9999);
        $json = ['object_kind' => 'push', 'rand' => $rand];
        $this->client->get('/event-delegation.php', json_encode($json))
            ->saveResponseHandler($handler);
        $this->assertEquals(json_encode($json) . json_encode($json), $handler->getFilteredResponse());
    }

}