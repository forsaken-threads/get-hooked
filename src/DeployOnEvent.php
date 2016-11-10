<?php namespace ForsakenThreads\GetHooked;

use Exception;

class DeployOnEvent implements CommandInterface, EventReceiverInterface, QueueReceiverInterface  {

    use EventReceiverFluentSetter,
        QueueAccess;


    // Branch to pull down
    protected $branch;

    // Path to the repo
    protected $path;

    // Command to run after deployment
    protected $postCommand = [];

    // Remote to pull from
    protected $remote;

    /**
     *
     * Run a command received off the queue
     *
     * In other words, deploy changes to the git repo at the given directory.
     * The `remote` and `branch` are configurable, and commands can be given to run after the git deployment.
     *
     * @param array $payload
     *
     * @return bool
     */
    static public function run($payload)
    {
        if (is_dir($payload['path'])) {
            $remote = escapeshellarg($payload['remote']);
            $branch = escapeshellarg($payload['branch']);
            exec("cd {$payload['path']} && git pull $remote $branch");
            foreach ($payload['postCommand'] as $command) {
                exec(escapeshellcmd($command));
            }
        }
        return true;
    }

    /**
     *
     * DeployOnMerge constructor.
     *
     * @param $eventType
     * @param $path
     * @param string $branch
     * @param string $remote
     * @param string|array $postCommand
     *
     * @throws Exception
     */
    public function __construct($eventType, $path, $branch = 'master', $remote = 'origin', $postCommand = '')
    {
        $this->eventType = $eventType;
        if (!is_dir($path)) {
            throw new Exception(__CLASS__ . ' constructor argument must be a valid directory.');
        }

        $this->toBranch = $branch;

        $this->path = $path;
        $this->branch = $branch;
        $this->remote = $remote;
        if ($postCommand) {
            $postCommand = (array) $postCommand;
            foreach ($postCommand as $command) {
                $this->postCommand[] = $command;
            }
        }
    }

    /**
     *
     * Receive the event and do something with it.
     *
     */
    public function handle()
    {
        // We use the path as the reference as it should be unique for deployment events
        $this->queue->singleton(self::class, $this->path, [
            'path' => $this->path,
            'remote' => $this->remote,
            'branch' => $this->branch,
            'postCommand' => $this->postCommand,
        ]);
    }

}