<?php namespace ForsakenThreads\GetHooked;

use Evenement\EventEmitter;

class Handler {

    /**
     * @var bool Boolean that indicates if the Gitlab secret was confirmed
     */
    protected $authenticated = false;

    /**
     * @var EventEmitter This emits events related to the webhook
     */
    protected $dispatcher;

    /**
     * @var string The event contained in the `X-Gitlab-Hook` header
     */
    protected $mainEvent;

    /**
     * @var array The JSON request parsed into an array
     */
    protected $hook;

    /**
     * @var string The webhook secret that authenticates the request
     */
    protected $secret;

    /**
     * Handler constructor, accepts the secret that authenticates the webhook from GitLab.
     *
     * @param $secret
     */
    public function __construct($secret)
    {
        // This grabs the body of the request.  It can only be read once, so we do it here and save for later.
        $this->hook = file_get_contents('php://input');

        $this->secret = $secret;
        $this->dispatcher = new EventEmitter();

        // Check for the token header from GitLab and determine if the request is authentic
        $token = isset($_SERVER['HTTP_X_GITLAB_TOKEN']) ? $_SERVER['HTTP_X_GITLAB_TOKEN'] : false;
        $this->authenticated = $token && ($token == $secret);
    }

    public function on($event, callable $listener)
    {
        $this->dispatcher->on($event, $listener);
        return $this;
    }

    /**
     *
     * Processes the request, and if valid, emits the appropriate emits
     *
     */
    public function receiveHook()
    {
        // Not an authenticated request, so we bail
        if (!$this->authenticated) {
            return;
        }

        // No event header, so we bail again
        if (! $this->mainEvent = isset($_SERVER['HTTP_X_GITLAB_EVENT']) ? $_SERVER['HTTP_X_GITLAB_EVENT'] : false) {
            return;
        };

        // Attempt to decode the GitLab hook JSON. If invalid, bail
        $hook = json_decode($this->hook, true);
        if (!$hook) {
            return;
        }

        var_dump($hook);
    }
}