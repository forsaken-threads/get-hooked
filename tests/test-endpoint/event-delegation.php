<?php

use ForsakenThreads\GetHooked\WebhookHandler;

include "../../vendor/autoload.php";

$handler = new WebhookHandler('ABC123');

$handler->onAny(function ($eventName, $event) {
    echo json_encode($event);
});

$handler->on('push', function($event) {
    echo json_encode($event);
});

$handler->receiveHook();