# Event Receivers

## The Basics

At a minimum, an Event Receiver must implement `EventReceiverInterface` which has two methods, `getEventType()` and `receive($event, $eventType = null)`.  If the implementation returns false for `getEventType()`, the receiver will be triggered for all event types.  If it returns a string, however, it will only be called on event types that match that string. 

`$eventType` contains the value sent in the GitLab hook for the key `object_kind`.  It can be `push`, `merge_request`, `note`, `issue` or any of a number of other things.  `$event` is an associative array that was converted from the full GitLab JSON hook.

When a webhook is received, the `$eventType` is determined.  All receivers that are listening on every event are called, and then listeners that are listening only for these types are called.