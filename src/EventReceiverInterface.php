<?php namespace ForsakenThreads\GetHooked;

interface EventReceiverInterface {

    /**
     *
     * Get event type for which this receiver listens
     *
     * A `boolean` false will listen for all events.
     *
     * @return boolean|string
     */
    public function getEventType();

    /**
     *
     * Receive an event from a WebhookHandler
     *
     * The `$eventType` will only be populated if this receiver listens to all events.
     * This is done by implementing the interface method `getEventType()` with a function that returns false.
     *
     * @param $event
     * @param $eventType
     *
     * @return mixed
     */
    public function receive($event, $eventType = null);

}