<?php namespace ForsakenThreads\GetHooked\Tests;

use ForsakenThreads\Diplomatic\Client;
use ForsakenThreads\Diplomatic\ResponseHandler;
use ForsakenThreads\Diplomatic\SelfHandling;
use ForsakenThreads\Diplomatic\Support\BasicFilters;
use PHPUnit\Framework\TestCase;

class Handler extends ResponseHandler {

    function wasErrored()
    {
        return $this->filteredResponse == 'Error';
    }

    function wasFailed()
    {
        return $this->filteredResponse == 'Failed';
    }

    function wasSuccessful()
    {
        return !$this->wasErrored() && !$this->wasFailed();
    }
}

class SelfHandler extends Handler implements SelfHandling {

    function onError()
    {
        return 'WasErrored';
    }

    function onFailure()
    {
        return 'WasFailed';
    }

    function onSuccess()
    {
        return 'WasSuccessful';
    }
}

class BaseTest extends TestCase {

    protected $client;

    protected $request = <<<EOH
{
    "object_kind": "push",
    "event_name": "push",
    "before": "e8f7d29b1a15037d24f8f74a4669898f938ce3db",
    "after": "2607ab8805f987fa1c63e20c89bb003d2b074c3d",
    "ref": "refs/heads/master",
    "checkout_sha": "2607ab8805f987fa1c63e20c89bb003d2b074c3d",
    "message": null,
    "user_id": 426951,
    "user_name": "Keith Freeman",
    "user_email": "gitlab@code550.info",
    "user_avatar": "https://gitlab.com/uploads/user/avatar/426951/avatar.png",
    "project_id": 1837484,
    "project": {
        "name": "Forsaken Threads Website",
        "description": "Online business card",
        "web_url": "https://gitlab.com/keithfreeman/forsaken-threads",
        "avatar_url": null,
        "git_ssh_url": "git@gitlab.com:keithfreeman/forsaken-threads.git",
        "git_http_url": "https://gitlab.com/keithfreeman/forsaken-threads.git",
        "namespace": "keithfreeman",
        "visibility_level": 0,
        "path_with_namespace": "keithfreeman/forsaken-threads",
        "default_branch": "master",
        "homepage": "https://gitlab.com/keithfreeman/forsaken-threads",
        "url": "git@gitlab.com:keithfreeman/forsaken-threads.git",
        "ssh_url": "git@gitlab.com:keithfreeman/forsaken-threads.git",
        "http_url": "https://gitlab.com/keithfreeman/forsaken-threads.git"
    },
    "commits": [
        {
            "id": "2607ab8805f987fa1c63e20c89bb003d2b074c3d",
            "message": "add packagist\\n",
            "timestamp": "2016-10-24T11:30:55-04:00",
            "url": "https://gitlab.com/keithfreeman/forsaken-threads/commit/2607ab8805f987fa1c63e20c89bb003d2b074c3d",
            "author": {
                "name": "Keith Freeman",
                "email": "ee6515@wayne.edu"
            },
            "added": [],
            "modified": [
                "basic-connection.php"
            ],
            "removed": []
        },
        {
            "id": "e8f7d29b1a15037d24f8f74a4669898f938ce3db",
            "message": "intial commit\\n",
            "timestamp": "2016-10-13T09:55:45-04:00",
            "url": "https://gitlab.com/keithfreeman/forsaken-threads/commit/e8f7d29b1a15037d24f8f74a4669898f938ce3db",
            "author": {
                "name": "Keith Freeman",
                "email": "ee6515@wayne.edu"
            },
            "added": [
                "favicon.ico",
                "basic-connection.php",
                "style.css",
                "waves.png"
            ],
            "modified": [],
            "removed": []
        }
    ],
    "total_commits_count": 2,
    "repository": {
        "name": "Forsaken Threads Website",
        "url": "git@gitlab.com:keithfreeman/forsaken-threads.git",
        "description": "Online business card",
        "homepage": "https://gitlab.com/keithfreeman/forsaken-threads",
        "git_http_url": "https://gitlab.com/keithfreeman/forsaken-threads.git",
        "git_ssh_url": "git@gitlab.com:keithfreeman/forsaken-threads.git",
        "visibility_level": 0
    }
}
EOH;

    public function __construct()
    {
        $handler = new Handler();
        $handler->filter([BasicFilters::class, 'json'], true);
        $this->client = new Client('http://localhost:8888', $handler);
        $this->client->addHeaders([
            'X-GitLab-Event' => 'Test Hook',
            'X-GitLab-Token' => 'ABC123',
            'Content-Type' => 'application/json',
        ]);
        parent::__construct();
    }

}

