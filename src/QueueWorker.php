<?php namespace ForsakenThreads\GetHooked;

class QueueWorker {

    /**
     * @var QueueManager
     */
    protected $queue;

    /**
     * QueueWorker constructor.
     *
     * @param $storagePath
     */
    public function __construct($storagePath)
    {
        $this->queue = new QueueManager($storagePath);
    }

    /**
     *
     * Work the queue
     */
    public function work()
    {
        $queues = $this->queue->getActiveQueues();
        /**
         * @var CommandInterface $workerClass
         * @var mixed $items
         */
        foreach ($queues as $workerClass => $items) {
            foreach ($items as $item) {
                $payload = $this->queue->getQueueItem($workerClass, $item);
                $result = $workerClass::run($payload);
                if ($result) {
                    $this->queue->removeQueueItem($workerClass, $item);
                }
            }
        }
    }
}