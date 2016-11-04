<?php namespace ForsakenThreads\GetHooked;

trait QueueAccess {

    /**
     * @var QueueManager
     */
    protected $queue;

    /**
     *
     * Set the storage instance
     *
     * @param QueueManager $queue
     *
     * @return $this
     */
    public function setQueue(QueueManager $queue)
    {
        $this->queue = $queue;
        return $this;
    }

}