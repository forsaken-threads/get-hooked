<?php namespace ForsakenThreads\GetHooked\Tests\EventReceivers;

use ForsakenThreads\GetHooked\EventReceiverFluentSetter;

class GenericEcho {

    use EventReceiverFluentSetter;

    /**
     *
     * Receive the event and do something with it.
     *
     */
    public function receive()
    {
        echo json_encode($this->event);
    }
}