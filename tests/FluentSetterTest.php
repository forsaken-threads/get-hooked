<?php namespace ForsakenThreads\GetHooked\Tests;

use Flintstone\Flintstone;
use ForsakenThreads\GetHooked\GitLabHook;

class FluentSetterTest extends BaseTest {

    public function tearDown()
    {
        parent::setUp();
        foreach (glob(__DIR__ . '/test-storage/*.dat') as $store) {
            unlink($store);
            $store = substr(strrchr($store, '/'), 1, -4);
            Flintstone::unload($store);
        }
        clearstatcache();
    }

    public function testFluentSetterNoCriteria()
    {
        /** @var Handler $handler */
        $json = [GitLabHook::EVENT_TYPE => rand(100, 999)];
        $this->client->get('/fluent-setters/generic-echo.php', json_encode($json))
            ->saveResponseHandler($handler);
        $this->assertEquals($json, $handler->getFilteredResponse());

        $json = [GitLabHook::EVENT_TYPE => rand(100, 999)];
        $this->client->get('/fluent-setters/generic-echo.php', json_encode($json))
            ->saveResponseHandler($handler);
        $this->assertEquals($json, $handler->getFilteredResponse());

        $json = [GitLabHook::EVENT_TYPE => rand(100, 999)];
        $this->client->get('/fluent-setters/generic-echo.php', json_encode($json))
            ->saveResponseHandler($handler);
        $this->assertEquals($json, $handler->getFilteredResponse());
    }

    public function testFluentSetterEventTypeCriterion()
    {
        /** @var Handler $handler */
        $json = [GitLabHook::EVENT_TYPE => 'push', 'rand' => rand(100, 999)];
        $this->client->get('/fluent-setters/on-push.php', json_encode($json))
            ->saveResponseHandler($handler);
        $this->assertEquals($json, $handler->getFilteredResponse());

        $json = [GitLabHook::EVENT_TYPE => 'merge_request', 'rand' => rand(100, 999)];
        $this->client->get('/fluent-setters/on-push.php', json_encode($json))
            ->saveResponseHandler($handler);
        var_dump($handler->getFilteredResponse());
        $this->assertEquals(['rejected' => $json], $handler->getFilteredResponse());
    }

    public function testFluentSetterForRepoCriterion()
    {
        /** @var Handler $handler */
        $json = [GitLabHook::EVENT_TYPE => 'push', GitLabHook::REPOSITORY => [GitLabHook::REPOSITORY_NAME => 'testing/testing'], 'rand' => rand(100, 999)];
        $this->client->get('/fluent-setters/on-push-for-repo.php', json_encode($json))
            ->saveResponseHandler($handler);
        $this->assertEquals($json, $handler->getFilteredResponse());

        $json = [GitLabHook::EVENT_TYPE => 'push', GitLabHook::REPOSITORY => [GitLabHook::REPOSITORY_NAME => 'testing/not-testing'], 'rand' => rand(100, 999)];
        $this->client->get('/fluent-setters/on-push-for-repo.php', json_encode($json))
            ->saveResponseHandler($handler);
        $this->assertEquals(['rejected' => $json], $handler->getFilteredResponse());

        $json = [GitLabHook::EVENT_TYPE => 'push', 'rand' => rand(100, 999)];
        $this->client->get('/fluent-setters/on-push-for-repo.php', json_encode($json))
            ->saveResponseHandler($handler);
        $this->assertEquals(['rejected' => $json], $handler->getFilteredResponse());
    }

    public function testFluentSetterByUsernameCriterion()
    {
        /** @var Handler $handler */
        $json = [GitLabHook::EVENT_TYPE => 'push', GitLabHook::USER => [GitLabHook::USER_NAME => 'testing-user'], 'rand' => rand(100, 999)];
        $this->client->get('/fluent-setters/on-push-by-user.php', json_encode($json))
            ->saveResponseHandler($handler);
        $this->assertEquals($json, $handler->getFilteredResponse());

        $json = [GitLabHook::EVENT_TYPE => 'push', GitLabHook::USER => [GitLabHook::USER_NAME => 'not-testing-user'], 'rand' => rand(100, 999)];
        $this->client->get('/fluent-setters/on-push-by-user.php', json_encode($json))
            ->saveResponseHandler($handler);
        $this->assertEquals(['rejected' => $json], $handler->getFilteredResponse());

        $json = [GitLabHook::EVENT_TYPE => 'push', GitLabHook::USERNAME => 'testing-user', 'rand' => rand(100, 999)];
        $this->client->get('/fluent-setters/on-push-by-user.php', json_encode($json))
            ->saveResponseHandler($handler);
        $this->assertEquals($json, $handler->getFilteredResponse());

        $json = [GitLabHook::EVENT_TYPE => 'push', GitLabHook::USERNAME => 'not-testing-user', 'rand' => rand(100, 999)];
        $this->client->get('/fluent-setters/on-push-by-user.php', json_encode($json))
            ->saveResponseHandler($handler);
        $this->assertEquals(['rejected' => $json], $handler->getFilteredResponse());

        $json = [GitLabHook::EVENT_TYPE => 'push', 'rand' => rand(100, 999)];
        $this->client->get('/fluent-setters/on-push-by-user.php', json_encode($json))
            ->saveResponseHandler($handler);
        $this->assertEquals(['rejected' => $json], $handler->getFilteredResponse());
    }

    public function testFluentSetterFromBranchCriterion()
    {
        /** @var Handler $handler */
        $json = [GitLabHook::EVENT_TYPE => 'push', GitLabHook::MERGE_REQUEST => [GitLabHook::SOURCE_BRANCH => 'testing'], 'rand' => rand(100, 999)];
        $this->client->get('/fluent-setters/on-push-from-branch.php', json_encode($json))
            ->saveResponseHandler($handler);
        $this->assertEquals($json, $handler->getFilteredResponse());

        $json = [GitLabHook::EVENT_TYPE => 'push', GitLabHook::MERGE_REQUEST => [GitLabHook::SOURCE_BRANCH => 'not-testing'], 'rand' => rand(100, 999)];
        $this->client->get('/fluent-setters/on-push-from-branch.php', json_encode($json))
            ->saveResponseHandler($handler);
        $this->assertEquals(['rejected' => $json], $handler->getFilteredResponse());

        $json = [GitLabHook::EVENT_TYPE => 'push', GitLabHook::MERGE_REQUEST => [], 'rand' => rand(100, 999)];
        $this->client->get('/fluent-setters/on-push-from-branch.php', json_encode($json))
            ->saveResponseHandler($handler);
        $this->assertEquals(['rejected' => $json], $handler->getFilteredResponse());

        $json = [GitLabHook::EVENT_TYPE => 'push', GitLabHook::OBJECT_ATTRIBUTES => [GitLabHook::SOURCE_BRANCH => 'testing'], 'rand' => rand(100, 999)];
        $this->client->get('/fluent-setters/on-push-from-branch.php', json_encode($json))
            ->saveResponseHandler($handler);
        $this->assertEquals($json, $handler->getFilteredResponse());

        $json = [GitLabHook::EVENT_TYPE => 'push', GitLabHook::OBJECT_ATTRIBUTES => [GitLabHook::SOURCE_BRANCH => 'not-testing'], 'rand' => rand(100, 999)];
        $this->client->get('/fluent-setters/on-push-from-branch.php', json_encode($json))
            ->saveResponseHandler($handler);
        $this->assertEquals(['rejected' => $json], $handler->getFilteredResponse());

        $json = [GitLabHook::EVENT_TYPE => 'push', GitLabHook::OBJECT_ATTRIBUTES => [], 'rand' => rand(100, 999)];
        $this->client->get('/fluent-setters/on-push-from-branch.php', json_encode($json))
            ->saveResponseHandler($handler);
        $this->assertEquals(['rejected' => $json], $handler->getFilteredResponse());

        $json = [GitLabHook::EVENT_TYPE => 'push', 'rand' => rand(100, 999)];
        $this->client->get('/fluent-setters/on-push-from-branch.php', json_encode($json))
            ->saveResponseHandler($handler);
        $this->assertEquals(['rejected' => $json], $handler->getFilteredResponse());
    }

    public function testFluentSetterToBranchCriterion()
    {
        /** @var Handler $handler */
        $json = [GitLabHook::EVENT_TYPE => 'push', GitLabHook::MERGE_REQUEST => [GitLabHook::TARGET_BRANCH => 'testing'], 'rand' => rand(100, 999)];
        $this->client->get('/fluent-setters/on-push-to-branch.php', json_encode($json))
            ->saveResponseHandler($handler);
        $this->assertEquals($json, $handler->getFilteredResponse());

        $json = [GitLabHook::EVENT_TYPE => 'push', GitLabHook::MERGE_REQUEST => [GitLabHook::TARGET_BRANCH => 'not-testing'], 'rand' => rand(100, 999)];
        $this->client->get('/fluent-setters/on-push-to-branch.php', json_encode($json))
            ->saveResponseHandler($handler);
        $this->assertEquals(['rejected' => $json], $handler->getFilteredResponse());

        $json = [GitLabHook::EVENT_TYPE => 'push', GitLabHook::MERGE_REQUEST => [], 'rand' => rand(100, 999)];
        $this->client->get('/fluent-setters/on-push-to-branch.php', json_encode($json))
            ->saveResponseHandler($handler);
        $this->assertEquals(['rejected' => $json], $handler->getFilteredResponse());

        $json = [GitLabHook::EVENT_TYPE => 'push', GitLabHook::OBJECT_ATTRIBUTES => [GitLabHook::TARGET_BRANCH => 'testing'], 'rand' => rand(100, 999)];
        $this->client->get('/fluent-setters/on-push-to-branch.php', json_encode($json))
            ->saveResponseHandler($handler);
        $this->assertEquals($json, $handler->getFilteredResponse());

        $json = [GitLabHook::EVENT_TYPE => 'push', GitLabHook::OBJECT_ATTRIBUTES => [GitLabHook::TARGET_BRANCH => 'not-testing'], 'rand' => rand(100, 999)];
        $this->client->get('/fluent-setters/on-push-to-branch.php', json_encode($json))
            ->saveResponseHandler($handler);
        $this->assertEquals(['rejected' => $json], $handler->getFilteredResponse());

        $json = [GitLabHook::EVENT_TYPE => 'push', GitLabHook::OBJECT_ATTRIBUTES => [GitLabHook::PIPELINE_BRANCH => 'testing'], 'rand' => rand(100, 999)];
        $this->client->get('/fluent-setters/on-push-to-branch.php', json_encode($json))
            ->saveResponseHandler($handler);
        $this->assertEquals($json, $handler->getFilteredResponse());

        $json = [GitLabHook::EVENT_TYPE => 'push', GitLabHook::OBJECT_ATTRIBUTES => [GitLabHook::PIPELINE_BRANCH => 'not-testing'], 'rand' => rand(100, 999)];
        $this->client->get('/fluent-setters/on-push-to-branch.php', json_encode($json))
            ->saveResponseHandler($handler);
        $this->assertEquals(['rejected' => $json], $handler->getFilteredResponse());

        $json = [GitLabHook::EVENT_TYPE => 'push', GitLabHook::OBJECT_ATTRIBUTES => [], 'rand' => rand(100, 999)];
        $this->client->get('/fluent-setters/on-push-to-branch.php', json_encode($json))
            ->saveResponseHandler($handler);
        $this->assertEquals(['rejected' => $json], $handler->getFilteredResponse());

        $json = [GitLabHook::EVENT_TYPE => 'push', 'rand' => rand(100, 999)];
        $this->client->get('/fluent-setters/on-push-to-branch.php', json_encode($json))
            ->saveResponseHandler($handler);
        $this->assertEquals(['rejected' => $json], $handler->getFilteredResponse());
    }

}