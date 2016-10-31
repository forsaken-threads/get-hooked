<?php namespace ForsakenThreads\GetHooked;

class GitLabHook {

    const EVENT_TYPE = 'object_kind';
    const BRANCH = 'ref';
    const MERGE_REQUEST = 'merge_request';
    const OBJECT_ATTRIBUTES = 'object_attributes';
    const PIPELINE_BRANCH = 'ref';
    const REPOSITORY = 'project';
    const REPOSITORY_NAME = 'path_with_namespace';
    const SOURCE_BRANCH = 'source_branch';
    const TARGET_BRANCH = 'target_branch';
    const USER = 'user';
    const USER_NAME = 'name';
    const USERNAME = 'username';

}