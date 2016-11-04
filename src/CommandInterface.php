<?php namespace ForsakenThreads\GetHooked;

interface CommandInterface {

    /**
     *
     * Run the command using the supplied payload
     *
     * Returns a boolean indicating true for removal from the queue or false to put it back on the queue
     *
     * @param $payload
     *
     * @return boolean
     */
    static public function run($payload);

}