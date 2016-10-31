<?php namespace ForsakenThreads\GetHooked\Tests\EventReceivers;

use ForsakenThreads\GetHooked\DeployOnPush;

class DeployOnPushTest extends DeployOnPush {

    public function reject()
    {
        echo json_encode(['rejected' => $this->event]);
    }

}