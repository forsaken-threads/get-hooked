<?php

use ForsakenThreads\GetHooked\WebhookHandler;

include "../../vendor/autoload.php";

$handler = new WebhookHandler('ABC123', __DIR__ . '/../test-storage');

$handler->receiveHook();