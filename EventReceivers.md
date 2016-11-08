# **Event Receivers**

## The Basics

In the terminology of this package, a *GitLab* webhook is the same as an event.  When you do something in *GitLab* that triggers a webhook, the [`WebhookHandler`](./src/WebhookHandler.php) will receive a JSON string from *GitLab*, parse it, and then use an **Event Emitter** to broadcast the webhook as an event.  **Event Receivers** are registered with the `WebhookHandler` which in turn registers them with the **Event Emitter**.

At a minimum, an **Event Receiver** must implement [`EventReceiverInterface`](./src/EventReceiverInterface.php) which has two methods, `getEventType()` and `receive($event, $eventType = null)`.  If the implementation returns false for `getEventType()`, the receiver will be triggered for all event types.  If it returns a string, however, it will only be called on event types that match that string.  When `receive()` is called, `$event` will be an associative array that was converted from the JSON hook that *GitLab* sent.  If the **Event Receiver** receives all event types, `$eventType` contains the value sent in the JSON hook for the `object_kind` key.  This value can be `push`, `merge_request`, `note`, `issue` or any of a number of other things. See the [*GitLab* webhook docs](https://*GitLab*.com/help/web_hooks/web_hooks) for more information.

When a webhook is received, the `$eventType` is determined.  All receivers that are listening on every event are called, and then listeners that are listening only for these types are called.  The **Event Receivers** can react to the event in real-time, doing whatever you want them to do when their `receive()` method is called.  They can also queue up a **Command** for the [`QueueWorker`](./src/QueueWorker.php) to work on asynchronously.  If you will only ever be reacting to webhooks in real-time, there's nothing else you need to do.

## Using the **Queue**

If you want your **Event Receiver** to queue up **Commands**, then it needs to implement the [`QueueReceiverInterface`](./src/QueueReceiverInterface.php) .  This interface has a single method, `setQueue()`, that receives an instance of [`QueueManager`](./src/QueueManager.php).  The **Event Receiver** should save it for later use when the `receive()` method is called with an event.  There is a trait included in this package, [`QueueAccess`](./src/QueueAccess.php), that you can put to use in your classes.  It implements the `setQueue()` method by saving the `QueueManager` instance as a `protected` property named `$queue`.

Once an event comes along and the `receive()` method on the **Event Receiver** is called, there are two ways to queue up a **Command**.  If the event should always trigger a **Command** for every instance, then the `queueUp()` method on the **Queue Manager** is used.  This method takes two arguments, the fully namespaced class of the **Command Worker** and a payload to be passed to the worker.  If the event should only ever trigger a single **Command** between **Queue** runs, then the `singleton()` method on the **Queue Manager** is used.  This method takes three arguments, again the class name of the worker, a reference string to uniquely identify the **Command**, and then finally the payload for the worker.  For **Singletons** the payload will get overwritten each time the **Command** is queued, but in practice, it is likely that the payload will always be the same.  Here's an example of each type used by a fictional **Event Receiver** (that is practically useless):

```
<?php

use ForsakenThreads\GetHooked\EventReceiverInterface;
use ForsakenThreads\GetHooked\QueueAccess;
use ForsakenThreads\GetHooked\QueueReceiverInterface;

class AcmeReceiver implements EventReceiverInterface, QueueReceiverInterface {

    use QueueAccess;
    
    public function getEventType()
    {
        return false;
    }
    
    public function receive($event, $eventType)
    {
        // at the time of this writing, this would only eliminate merge request events
        if (empty($event['project']['name'])) {
            return;
        }
        // This singleton simply keeps track of whether anything happens
        // between queue runs for each project that sends us webhooks
        $this->queue->singleton(AcmeSingleton::class, $event['project']['name'], 'something happened to ' . $event['project']['name']);
        
        // This will record the type of event for all projects for every event 
         $this->queue->queueUp(AcmeWorker::class, [$event['project']['name'] => $eventType]);
     }
}
```

## Fluently setting the **Event Receiver**

This package includes a trait, [`EventReceiverFluentSetter`](./src/EventReceiverFluentSetter.php), that will allow you to easily filter out specific events.  For example, if you have several repositories sending out webhooks, you may need a receiver to handle very specific events.  The **Fluent Setter** trait exposes a number of methods that make setting these filter criteria very easy.  Here are the methods available:
* `forRepo($repo)` - this will limit the receiver to only reacting to events related to this specific repo (uses vendor-name/project-name format)
* `from($branch)` - this will limit based on the from, or source branch, on a merge request event or on a comment about a merge request
* `initiatedBy($username)` - this will limit based on the user that initiates the event the triggered the webhook, formatted as the formal name, e.g. 'Keith Freeman'
* `on($eventType)` - this will limit based on the eventType, as defined by the `object_kind` key in the JSON that GitLab sends
* `to($branch)` - this will limit based on the to, or target branch, useful for push events, merge requests or comments regarding a merge request, or other events that relate to a specific branch like pipeline events

The **Fluent Setter** works by implementing the `receive()` method for the **Event Receiver** and inspecting the `$event` for matching criteria.  If there is no match, the event is simply dropped.  If there is a match, however, the setter trait needs a way to trigger the related class with the event data.  For this reason, the `EventReceiverFluentSetter` trait declares an abstract public function `handle()`.  The using class must implement this abstract function.  It does not receive any arguments because the event was already received.  It is available as a `protected` property on the class called `$event`.

## **Command Workers**

The **Command Workers** that receive payloads off the **Queue** can be separate classes, as shown in the example above.  The can also be the same class as the **Event Receiver**, something that the two **Workers** included with this package do ([`DeployOnPush`](./src/DeployOnPush.php) and [`DeployOnMerge`](./src/DeployOnMerge.php)).  In either case, the **Worker** must implement the [`CommandInterface`](./src/CommandInterface.php) which has a single static method, `run()`, that receives a `$payload` off the **Queue**.  This method should return a boolean value that determines whether the queued command should be returned to the **Queue** or can be safely removed.  Continuing the above example:
 
```
<?php

use ForsakenThreads\GetHooked\CommandInterface;

class AcmeSingleton implements CommandInterface {
 
    static public function run($payload)
    {
        Twitter::tweet("Yay $payload! #GitLabWebhooks");
    }
}
```