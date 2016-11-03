<?php

use ForsakenThreads\GetHooked\Tests\EventReceivers\DeployOnPushTest;
use ForsakenThreads\GetHooked\WebhookHandler;

include "../../../vendor/autoload.php";

$handler = new WebhookHandler('ABC123', __DIR__ . '/../../test-storage');

$receiver = new DeployOnPushTest();
$receiver->initiatedBy('testing-user');

$handler->addReceiver($receiver);
$handler->receiveHook();
