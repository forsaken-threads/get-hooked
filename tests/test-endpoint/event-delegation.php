<?php

use ForsakenThreads\GetHooked\WebhookHandler;

include "../../vendor/autoload.php";

$handler = new WebhookHandler('ABC123', __DIR__ . '/../test-storage');

$handler->onAny(function ($event, $eventType) {
    echo json_encode($event);
});

$handler->on('push', function($event) {
    echo json_encode($event);
});

$handler->receiveHook();