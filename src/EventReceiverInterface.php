<?php namespace ForsakenThreads\GetHooked;

interface EventReceiverInterface {

    /**
     *
     * Receive an event from a WebhookHandler
     *
     * @param $eventType
     * @param $event
     * @return mixed
     */
    public function receive($eventType, $event);

}