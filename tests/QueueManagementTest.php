<?php namespace ForsakenThreads\GetHooked\Tests;

use Flintstone\Flintstone;
use ForsakenThreads\GetHooked\QueueManager;
use ForsakenThreads\GetHooked\Tests\EventReceivers\AsyncLoggerTest;
use ForsakenThreads\GetHooked\Tests\EventReceivers\DeployOnPushTest;
use PHPUnit\Framework\TestCase;

class QueueManagementTest extends TestCase {

    /** @var QueueManager */
    protected $manager;

    public function setUp()
    {
        parent::setUp();
        $this->manager = new QueueManager(__DIR__ . '/test-storage');
    }

    public function tearDown()
    {
        parent::tearDown();
        foreach (glob(__DIR__ . '/test-storage/*.dat') as $store) {
            unlink($store);
            $store = substr(strrchr($store, '/'), 1, -4);
            Flintstone::unload($store);
        }
        clearstatcache();
    }

    public function testQueueUp()
    {
        $from = rand(0, 5);
        $to = rand($from + 1, 10);
        $test1 = range($from, $to);
        $this->manager->queueUp(AsyncLoggerTest::class, $test1);
        $active = $this->manager->getActiveQueues();
        $this->assertEquals(1, count($active[AsyncLoggerTest::class]));

        $from = rand(0, 5);
        $to = rand($from + 1, 10);
        $test2 = range($from, $to);
        $this->manager->queueUp(AsyncLoggerTest::class, $test2);
        $active = $this->manager->getActiveQueues();
        $this->assertEquals(2, count($active[AsyncLoggerTest::class]));

        $from = rand(0, 5);
        $to = rand($from + 1, 10);
        $test3 = range($from, $to);
        $this->manager->queueUp(AsyncLoggerTest::class, $test3);
        $active = $this->manager->getActiveQueues();
        $this->assertEquals(3, count($active[AsyncLoggerTest::class]));

        $tests = [$test1, $test2, $test3];
        $test = 0;
        foreach ($active as $workerClass => $references) {
            foreach ($references as $reference) {
                $item = $this->manager->getQueueItem($workerClass, $reference);
                $this->assertEquals($tests[$test], $item);
                $test++;
            }
        }
    }

    public function testSingleton()
    {
        $from = rand(0, 5);
        $to = rand($from + 1, 10);
        $test = range($from, $to);
        $this->manager->singleton(DeployOnPushTest::class, 'abc-test', $test);
        $active = $this->manager->getActiveQueues();
        $this->assertEquals([DeployOnPushTest::class => ['abc-test']], $active);

        $this->manager->singleton(DeployOnPushTest::class, 'abc-test', 'not staying here');
        $this->manager->singleton(DeployOnPushTest::class, 'abc-test', 'overridden');
        $this->manager->singleton(DeployOnPushTest::class, 'abc-test', $test);
        $active = $this->manager->getActiveQueues();
        $this->assertEquals([DeployOnPushTest::class => ['abc-test']], $active);

        $payload = $this->manager->getQueueItem(DeployOnPushTest::class, 'abc-test');
        $this->assertEquals($test, $payload);

        $this->manager->removeQueueItem(DeployOnPushTest::class, 'abc-test');
        $this->assertNull($this->manager->getQueueItem(DeployOnPushTest::class, 'abc-test'));
        $this->assertEquals([], $this->manager->getActiveQueues());

        $this->manager->singleton(DeployOnPushTest::class, 'abc-test', 'not staying here');
        $this->manager->singleton(DeployOnPushTest::class, 'abc-test1', 'should stay');
        $this->manager->singleton(DeployOnPushTest::class, 'abc-test', $test);
        $active = $this->manager->getActiveQueues();
        $this->assertEquals([DeployOnPushTest::class => ['abc-test', 'abc-test1']], $active);
    }
}