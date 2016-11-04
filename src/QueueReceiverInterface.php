<?php namespace ForsakenThreads\GetHooked;

interface QueueReceiverInterface {

    /**
     *
     * Receive an injected instance of the `QueueManager`
     *
     * @param QueueManager $queue
     */
    public function setQueue(QueueManager $queue);

}