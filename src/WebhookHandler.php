<?php namespace ForsakenThreads\GetHooked;

use AdammBalogh\KeyValueStore\Adapter\FileAdapter;
use AdammBalogh\KeyValueStore\KeyValueStore;
use Flintstone\Flintstone;
use Flintstone\FlintstoneDB;

class WebhookHandler {

    /**
     * @var bool Boolean that indicates if the GitLab secret was confirmed
     */
    protected $authenticated = false;

    /**
     * @var Emitter This emits events related to the webhook
     */
    protected $dispatcher;

    /**
     * @var array The JSON request parsed into an array
     */
    protected $hook;

    /**
     * @var string The event contained in the `X-GitLab-Hook` header
     */
    protected $mainEvent;

    /**
     * @var string The webhook secret that authenticates the request
     */
    protected $secret;

    /**
     * @var KeyValueStore
     */
    protected $storage;

    /**
     * Handler constructor, accepts the secret that authenticates the webhook from GitLab and the directory path for the event database.
     *
     * @param string $secret
     * @param string $storagePath
     */
    public function __construct($secret, $storagePath)
    {
        // This grabs the body of the request.  It can only be read once, so we do it here and save for later.
        $this->hook = file_get_contents('php://input');

        $this->secret = $secret;
        $this->dispatcher = new Emitter();

        // Check for the token header from GitLab and determine if the request is authentic
        $token = isset($_SERVER['HTTP_X_GITLAB_TOKEN']) ? $_SERVER['HTTP_X_GITLAB_TOKEN'] : false;
        $this->authenticated = $token && ($token === $secret);

        if ($this->authenticated) {
            $storageClient = Flintstone::load('get-hooked', ['dir' => $storagePath]);
            $this->storage = new KeyValueStore(new FileAdapter($storageClient));
        }
    }

    /**
     *
     * Register an implementation of EventReceiverInterface
     *
     * @param EventReceiverInterface $receiver
     */
    public function addReceiver(EventReceiverInterface $receiver)
    {
        $this->onAny([$receiver, 'receive']);
    }

    public function on($event, callable $listener)
    {
        $this->dispatcher->on($event, $listener);
        return $this;
    }

    public function onAny($listener)
    {
        $this->dispatcher->onAny($listener);
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
            http_response_code(401);
            return;
        }

        // No event header, so we bail
        if (! $this->mainEvent = isset($_SERVER['HTTP_X_GITLAB_EVENT']) ? $_SERVER['HTTP_X_GITLAB_EVENT'] : false) {
            http_response_code(403);
            return;
        };

        // Attempt to decode the GitLab hook JSON. If invalid, bail
        $this->hook = json_decode($this->hook, true);
        if (!$this->hook) {
            http_response_code(400);
            return;
        }

        // without `object_kind` we don't know what kind of event to emit
        if (! $event = $this->hook['object_kind']) {
            http_response_code(422);
            return;
        }

        // to emit the hook as a single object, we wrap it in an array
        $this->dispatcher->emit($event, [$this->hook]);
    }
}