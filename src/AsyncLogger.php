<?php namespace ForsakenThreads\GetHooked;

use Exception;

class AsyncLogger implements CommandInterface, EventReceiverInterface, QueueReceiverInterface  {

    use EventReceiverFluentSetter,
        QueueAccess;

    // Path to the log file
    protected $logFilePath;

    /**
     *
     * Run a command received off the queue
     *
     * In other words, log the webhook, which in this case is the $payload.
     *
     * @param string $payload
     *
     * @return bool
     */
    static public function run($payload)
    {
        if (is_file($payload['logFilePath']) && is_writable($payload['logFilePath'])) {
            $json = escapeshellarg($payload['json']);
            exec("echo $json >> {$payload['logFilePath']}");
        }
        return true;
    }

    /**
     * AsyncLogger constructor.
     *
     * @param $logFilePath
     *
     * @throws Exception
     */
    public function __construct($logFilePath)
    {
        if (!is_file($logFilePath)) {
            throw new Exception(__CLASS__ . ' constructor argument must be a valid file.');
        }

        $this->logFilePath = $logFilePath;
    }

    /**
     *
     * Receive the event and do something with it.
     *
     * Here we simply queue up the event as a JSON string for logging asynchronously.
     *
     */
    public function handle()
    {
        $this->queue->queueUp(self::class, ['logFilePath' => $this->logFilePath, 'json' => json_encode($this->event, JSON_PRETTY_PRINT)]);
    }

}