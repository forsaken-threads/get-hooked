<?php

use ForsakenThreads\GetHooked\Handler;

include "../../vendor/autoload.php";

$handler = new Handler('ABC123');

$handler->onAny(function ($eventName, $event) {
    echo json_encode($event);
});

$handler->on('on', function($event) {
    echo json_encode($event);
});

$handler->receiveHook();