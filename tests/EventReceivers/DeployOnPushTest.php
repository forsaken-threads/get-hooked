<?php namespace ForsakenThreads\GetHooked\Tests\EventReceivers;

use ForsakenThreads\GetHooked\DeployOnPush;

class DeployOnPushTest extends DeployOnPush {

    static public function run($payload)
    {
        echo $payload;
        return true;
    }

    public function handle()
    {
        echo json_encode($this->event);
    }

    public function reject()
    {
        echo json_encode(['rejected' => $this->event]);
    }
}