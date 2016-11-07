<?php namespace ForsakenThreads\GetHooked;

use Evenement\EventEmitter;

class Emitter extends EventEmitter {

    protected $anyListeners = [];

    /**
     *
     * Register listener for every event
     *
     * @param callable $listener
     */
    public function onAny($listener)
    {
        $this->anyListeners[] = $listener;
    }


    /**
     *
     * Override of parent `emit()` to handle `onAny` listeners
     *
     * @param $event
     * @param array $arguments
     */
    public function emit($event, array $arguments = [])
    {
        foreach ($this->anyListeners as $listener) {
            // this looks weird, but in order to send the `on` listeners a single object,
            // the hook object is wrapped in an array. so for the `onAny` listeners to
            // receive the object in the same way, we have to unwrap it
            call_user_func($listener, $arguments[0], $event);
        }
        parent::emit($event, $arguments);
    }

}