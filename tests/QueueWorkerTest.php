<?php namespace ForsakenThreads\GetHooked\Tests;

use ForsakenThreads\GetHooked\QueueManager;
use ForsakenThreads\GetHooked\QueueWorker;
use ForsakenThreads\GetHooked\Tests\EventReceivers\DeployOnPushTest;
use PHPUnit\Framework\TestCase;

class QueueWorkerTest extends TestCase {

    /** @var QueueManager */
    protected $manager;

    /** @var QueueWorker */
    protected $worker;

    public function setUp()
    {
        parent::setUp();
        $this->manager = new QueueManager(__DIR__ . '/test-storage');
        $this->worker = new QueueWorker(__DIR__ . '/test-storage');
    }

    public function tearDown()
    {
        parent::tearDown();
        foreach (glob(__DIR__ . '/test-storage/*.dat') as $store) {
            unlink($store);
        }
    }

    public function testSingleton()
    {
        $from = rand(0, 5);
        $to = rand($from + 1, 10);
        $test1 = "$from-$to";
        $from = rand(0, 5);
        $to = rand($from + 1, 10);
        $test2 = "$from-$to";
        $from = rand(0, 5);
        $to = rand($from + 1, 10);
        $test3 = "$from-$to";
        $this->manager->singleton(DeployOnPushTest::class, 'test1', $test1);
        $this->manager->singleton(DeployOnPushTest::class, 'test2', $test2);
        $this->manager->singleton(DeployOnPushTest::class, 'test3', $test3);
        ob_start();
        $this->worker->work();
        $output = ob_get_contents();
        ob_end_clean();
        $this->assertEquals("$test1$test2$test3", $output);
        $this->assertEquals([], $this->manager->getActiveQueues());
    }
}