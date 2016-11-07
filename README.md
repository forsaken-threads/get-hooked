## Get Hooked

### A simple-ish GitLab webhook handler

This is something I threw together to auto-deploy some static sites whenever I pushed to a private GitLab repo.  It's extensible and has some neat features but is really just a quick hack.

### One part handler, one part queue

Basically the webhook handler takes GitLab hooks and stores them in a local queue.  Then a locally run queue worker will pull them off the queue and respond to them at your leisure.  For example, if you get a flurry of pushes that need deployment to production, you probably don't want to pull down every change as it happens.  You can queue up that deployment and then do it once rather than responding to every trigger.

## Very basic setup

### Installation

`composer require forsaken-threads/get-hooked`

### The Webhook Handler

Put this somewhere public that GitLab can hit.

```
<?php

use ForsakenThreads\GetHooked\DeployOnPush;
use ForsakenThreads\GetHooked\WebhookHandler;

// Obviously use the path to your actual composer autoloader
include "../vendor/autoload.php";

// You must configure GitLab to send a token and include it here.
// Feel free to use an environmental variable or something else to keep it out of
// the web server directory.
// The second argument is a directory path to a folder that can be used to store 
// the queues.  It must be writable/readable by the web server and the queue worker.
$handler = new WebhookHandler('My GitLab Webhook Secret', '/path/to/storage');

// Register an Event Receiver with the handler.
// At a minimum you supply the repo path.  You can also supply a branch as the
// the second argument, default is `master`, or a remote as the third argument, default
// is `origin`.
$handler->addReceiver(new DeployOnPush('/path/to/repo'));

// Receive the hook from GitLab
// The handler will look for `push` events, and if it finds one, queue it up as
// a deploy command.
$handler->receiveHook();
```

Super easy, right?  This will respond to all `push` events from GitLab.  What if you wanted something a little more specific?  There's no need to deploy to `master` when there's a push to a different branch.  Event Receivers can be set fluently.  Here are some of the options:

```
<?php

$receiver = new DeployOnPush('/path/to/repo');
$receiver->initiatedBy('Keith Freeman')
    ->to('master')
    ->forRepo('keithfreeman/forsaken-threads-website');
```

Now the deployment will only occur when `Keith Freeman` pushes to the `master` branch on the `keithfreeman/forsaken-threads-website` repository.  All other events will be dropped.  Unless, of course, they are accepted by different Event Receivers.

### The Queue Worker

Put this somewhere logical and set up a `cron` job to run it as frequently as you like.

```
<?php

use ForsakenThreads\GetHooked\QueueWorker;

// Obviously use the path to your actual composer autoloader
include 'vendor/autoload.php';

// The argument is a directory path to a folder that can be used to store 
// the queues.  It must be writable/readable by the web server and the queue worker.
$worker = new QueueWorker('/path/to/storage');
$worker->work();
```

Also super easy, right?  This will pull commands off the queue and work them.

## Is that all?

### What else can it do?

At the moment, not much.  In theory, one could add Event Receivers to respond to other event types.  I am totally for doing that if somebody really wants one.  The only other receiver I have written is `DeployOnMerge` which works a lot like `DeployOnPush`.  If you want a tad more control over, say, a GitLab-Slack integration, you could totally write a handful of receivers and do whatever you wanted.  If they have broad enough appeal, I could list them here, or even include them in the repo.  That's what open source is all about, anyway, isn't it?  Sharing solutions.

### For more info

If you want to take a deeper dive, you can check out Event Receivers in depth [here](EventReceivers.md).  Or have a look at the SAMI docs [here](http://get-hooked.forsaken-threads.com).