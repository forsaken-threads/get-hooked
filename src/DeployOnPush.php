<?php namespace ForsakenThreads\GetHooked;

class DeployOnPush implements CommandInterface, EventReceiverInterface {

    use EventReceiverFluentSetter;

    protected $path;

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
    public function handle()
    {
    }

    /**
     *
     * Deploy the git repo at the given directory
     *
     * @param array $arguments
     */
    public function run(array $arguments)
    {

    }
}