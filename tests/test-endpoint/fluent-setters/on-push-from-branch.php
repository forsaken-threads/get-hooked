<?php

use ForsakenThreads\GetHooked\Tests\EventReceivers\DeployOnPushTest;
use ForsakenThreads\GetHooked\WebhookHandler;

include "../../../vendor/autoload.php";

$handler = new WebhookHandler('ABC123');

$receiver = new DeployOnPushTest();
$receiver->from('testing')
    ->set($handler);

$handler->receiveHook();
