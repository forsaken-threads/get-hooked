<?php namespace ForsakenThreads\GetHooked;

use AdammBalogh\KeyValueStore\Adapter\FileAdapter;
use AdammBalogh\KeyValueStore\KeyValueStore;
use Exception;
use Flintstone\Flintstone;

class QueueManager {

    /**
     * @var KeyValueStore[]
     */
    protected $stores;

    /**
     * @var string
     */
    protected $storagePath;

    /**
     *
     * QueueManager constructor.
     *
     * @param string $storagePath
     *
     * @throws Exception
     */
    public function __construct($storagePath)
    {
        if (!is_dir($storagePath) || !is_writable($storagePath)) {
            throw new Exception("$storagePath is not a valid path to a writable storage directory");
        }
        $this->storagePath = $storagePath;
        if (!$this->admin()->has('queues')) {
            $this->admin()->set('queues', []);
        }
    }

    /**
     *
     * Get the active queues with their pending items
     *
     * @return array
     */
    public function getActiveQueues()
    {
        if (!$this->admin()->has('queues')) {
            return [];
        }

        $active = [];
        $queues = $this->admin()->get('queues');
        foreach ($queues as $workerClass => $queue) {
            if (!$this->getStore($queue, false)->has('__queue')) {
                continue;
            }
            $items = $this->getStore($queue, false)->get('__queue');
            if (!empty($items)) {
                $active[$workerClass] = $items;
            }
        }

        return $active;
    }

    /**
     *
     * Get the payload for a queue item
     *
     * @param $workerClass
     * @param $reference
     *
     * @return mixed
     */
    public function getQueueItem($workerClass, $reference)
    {
        return $this->getStore($workerClass)->has($reference) ? $this->getStore($workerClass)->get($reference) : null;
    }

    /**
     *
     * Queue up a command for the specified worker.
     *
     * @param $workerClass
     * @param $data
     */
    public function queueUp($workerClass, $data)
    {
        $this->registerQueue($workerClass);
        $queued = $this->getStore($workerClass)->get('__queue');
        do {
            $reference = chr(rand(65, 90)) . rand(10, 100 + count($queued));
        } while (in_array($reference, $queued));
        $this->getStore($workerClass)->set($reference, $data);
        $queued[] = $reference;
        $this->getStore($workerClass)->set('__queue', $queued);
    }
    /**
     *
     * Remove an item from a queue
     *
     * @param $workerClass
     * @param $reference
     */
    public function removeQueueItem($workerClass, $reference)
    {
        $queue = $this->getStore($workerClass)->get('__queue');
        $this->getStore($workerClass)->set('__queue', array_values(array_diff($queue, [$reference])));
        $this->getStore($workerClass)->delete($reference);
    }

    /**
     *
     * Set a singleton queue item
     *
     * Singletons only exist once. For example, it makes no sense to schedule a deployment many times.
     * A single deployment catches up the repo and renders subsequent contemporaneous deployments unnecessary.
     *
     * @param $workerClass
     * @param $reference
     * @param mixed $data
     */
    public function singleton($workerClass, $reference, $data)
    {
        $this->registerQueue($workerClass);
        $this->getStore($workerClass)->set($reference, $data);
        $queued = $this->getStore($workerClass)->get('__queue');
        if (!in_array($reference, $queued)) {
            $queued[] = $reference;
            $this->getStore($workerClass)->set('__queue', $queued);
        }
    }

    /**
     *
     * Access the admin store
     *
     * @return KeyValueStore
     */
    protected function admin()
    {
        return $this->getStore('admin', false);
    }

    /**
     *
     * Get a specific `KeyValueStore`
     *
     * @param $name
     * @param bool $hash
     *
     * @return KeyValueStore
     */
    protected function getStore($name, $hash = true)
    {
        if ($hash) {
            $name = hash('md5', $name);
        }
        if (!empty($this->stores[$name])) {
            return $this->stores[$name];
        }

        $client = Flintstone::load($name, ['dir' => $this->storagePath]);
        return $this->stores[$name] = new KeyValueStore(new FileAdapter($client));
    }

    /**
     *
     * Register a queue if not already done so
     *
     * @param $queue
     */
    protected function registerQueue($queue)
    {
        $queues = $this->admin()->get('queues');
        if (!in_array($queue, array_keys($queues))) {
            $queues[$queue] = hash('md5', $queue);
            $this->admin()->set('queues', $queues);
            $this->getStore($queue)->set('__queue', []);
        }
    }
}