<?php namespace ForsakenThreads\GetHooked;

class DeployOnPush extends DeployOnEvent {

    public function __construct($path, $branch, $remote, $postCommand)
    {
        parent::__construct('push', $path, $branch, $remote, $postCommand);
    }
}