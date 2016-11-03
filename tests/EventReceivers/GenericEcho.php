<?php namespace ForsakenThreads\GetHooked\Tests\EventReceivers;

use ForsakenThreads\GetHooked\EventReceiverFluentSetter;
use ForsakenThreads\GetHooked\EventReceiverInterface;

class GenericEcho implements EventReceiverInterface {

    use EventReceiverFluentSetter;

    /**
     *
     * Receive the event and do something with it.
     *
     */
    public function handle()
    {
        echo json_encode($this->event);
    }
}