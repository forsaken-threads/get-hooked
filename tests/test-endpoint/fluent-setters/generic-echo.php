<?php

use ForsakenThreads\GetHooked\Tests\EventReceivers\GenericEcho;
use ForsakenThreads\GetHooked\WebhookHandler;

include "../../../vendor/autoload.php";

$handler = new WebhookHandler('ABC123');

$receiver = new GenericEcho();
$receiver->set($handler);

$handler->receiveHook();