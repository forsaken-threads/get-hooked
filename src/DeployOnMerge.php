<?php namespace ForsakenThreads\GetHooked;

class DeployOnMerge extends DeployOnEvent {

    public function __construct($path, $branch, $remote, $postCommand)
    {
        parent::__construct('merge_request', $path, $branch, $remote, $postCommand);
    }
}