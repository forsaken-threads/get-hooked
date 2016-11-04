<?php namespace ForsakenThreads\GetHooked;

use Exception;

class DeployOnPush implements CommandInterface, EventReceiverInterface, QueueReceiverInterface  {

    use EventReceiverFluentSetter,
        QueueAccess;

    protected $path;

    /**
     *
     * Run a command received off the queue
     *
     * In other words, deploy changes to the git repo at the given directory
     *
     * @param array $path
     *
     * @return bool
     */
    static public function run($path)
    {
        if (is_dir($path)) {
            exec(`cd $path && git checkout -f`);
        }
        return true;
    }

    /**
     *
     * DeployOnPush constructor.
     *
     * @param $path
     *
     * @throws Exception
     */
    public function __construct($path)
    {
        $this->eventType = 'push';
        if (!is_dir($path)) {
            throw new Exception(__CLASS__ . ' constructor argument must be a valid directory.');
        }

        $this->path = $path;
    }

    /**
     *
     * Receive the event and do something with it.
     *
     */
    public function handle()
    {
        // We just use the path as both the reference and the data on the singleton queue item.
        $this->queue->singleton(self::class, $this->path, $this->path);
    }

}