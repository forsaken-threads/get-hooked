<?php

use ForsakenThreads\GetHooked\Handler;

include "../../vendor/autoload.php";

$handler = new Handler('ABC123');
$handler->receiveHook();