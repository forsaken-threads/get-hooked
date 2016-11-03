<?php

use ForsakenThreads\GetHooked\Tests\EventReceivers\DeployOnPushTest;
use ForsakenThreads\GetHooked\WebhookHandler;

include "../../../vendor/autoload.php";

$handler = new WebhookHandler('ABC123', __DIR__ . '/../../test-storage');

$handler->addReceiver(new DeployOnPushTest());

$handler->receiveHook();
