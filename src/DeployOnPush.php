<?php namespace ForsakenThreads\GetHooked;

class DeployOnPush {

    use EventReceiverFluentSetter;

    /**
     *
     * DeployOnPush constructor.
     *
     */
    public function __construct()
    {
        $this->eventType = 'push';
    }

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