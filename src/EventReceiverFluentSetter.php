<?php namespace ForsakenThreads\GetHooked;

trait EventReceiverFluentSetter  {

    // Copy of a received event
    protected $event;

    // The GitLab event type, found in `object_kind` key within the JSON hook
    protected $eventType = false;

    // The secondarily related git branch for the event
    protected $fromBranch;

    // The GitLab repo related to the event
    protected $repository;

    // The git branch directly related to the event
    protected $toBranch;

    // The GitLab user that initiated the event cycle
    protected $username;

    /**
     *
     * Handle the received event.
     *
     */
    abstract public function handle();

    /**
     *
     * Apply repository criterion to the setter, in vendor-name/project-name format
     *
     * @param $repo
     *
     * @return $this
     */
    public function forRepo($repo)
    {
        $this->repository = @(string) $repo;
        return $this;
    }

    /**
     *
     * Apply from branch criterion to the setter
     *
     * @param $branch
     *
     * @return $this
     */
    public function from($branch)
    {
        $this->fromBranch = @(string) $branch;
        return $this;
    }

    /**
     *
     * Return the event type for which this receiver listens
     *
     * @return boolean|string
     */
    public function getEventType()
    {
        return $this->eventType;
    }

    /**
     *
     * Apply username criterion to the setter, formatted as the formal name, e.g. 'Keith Freeman'
     *
     * @param $username
     *
     * @return $this
     */
    public function initiatedBy($username)
    {
        $this->username = @(string) $username;
        return $this;
    }

    /**
     *
     * Called by the Event Emitter and will determine if the Event matches the set criteria
     *
     * The `$eventType` will only be populated if this receiver listens to all events.
     * This is done by implementing the interface method `getEventType()` with a function that returns false.
     *
     * @param array $event
     * @param string $eventType
     */
    public function receive($event, $eventType = null)
    {
        $this->event = $event;

        if (!$this->filterOnEvent() || !$this->filterOnUsername() || !$this->filterOnRepo() || !$this->filterOnToBranch() || !$this->filterOnFromBranch()) {
            if (method_exists($this, 'reject')) {
                $this->reject();
            }
            return;
        }

        $this->handle();
    }

    /**
     *
     * Apply eventType criterion to the setter
     *
     * @param $eventType
     *
     * @return $this
     */
    public function on($eventType)
    {
        $this->eventType = @(string) $eventType;
        return $this;
    }

    /**
     *
     * Apply to branch criterion to the setter
     *
     * @param $branch
     *
     * @return $this
     */
    public function to($branch)
    {
        $this->toBranch = @(string) $branch;
        return $this;
    }

    /**
     *
     * Checks if the eventType criterion is matched
     *
     * @return bool
     */
    protected function filterOnEvent()
    {
        if ($this->eventType && ($this->eventType != $this->event[GitLabHook::EVENT_TYPE])) {
            return false;
        }

        return true;
    }

    /**
     *
     * Checks if the from branch criterion is matched
     *
     * @return bool
     */
    protected function filterOnFromBranch()
    {
        if (!$this->fromBranch) {
            return true;
        }

        if (
            empty($this->event[GitLabHook::MERGE_REQUEST][GitLabHook::SOURCE_BRANCH]) &&
            empty($this->event[GitLabHook::OBJECT_ATTRIBUTES][GitLabHook::SOURCE_BRANCH])
        ) {
            return false;
        }

        if (!empty($this->event[GitLabHook::MERGE_REQUEST][GitLabHook::SOURCE_BRANCH]) && ($this->fromBranch != $this->event[GitLabHook::MERGE_REQUEST][GitLabHook::SOURCE_BRANCH])) {
            return false;
        }

        if (!empty($this->event[GitLabHook::OBJECT_ATTRIBUTES][GitLabHook::SOURCE_BRANCH]) && ($this->fromBranch != $this->event[GitLabHook::OBJECT_ATTRIBUTES][GitLabHook::SOURCE_BRANCH])) {
            return false;
        }

        return true;
    }

    /**
     *
     * Checks if the repository criterion is matched
     *
     * @return bool
     */
    protected function filterOnRepo()
    {
        if ($this->repository &&
            (
                empty($this->event[GitLabHook::REPOSITORY][GitLabHook::REPOSITORY_NAME]) || ($this->repository != $this->event[GitLabHook::REPOSITORY][GitLabHook::REPOSITORY_NAME])
            )
        ) {
            return false;
        }

        return true;
    }

    /**
     *
     * Checks if the to branch criterion is matched
     *
     * @return bool
     */
    protected function filterOnToBranch()
    {
        if (!$this->toBranch) {
            return true;
        }

        if (
            empty($this->event[GitLabHook::BRANCH]) &&
            empty($this->event[GitLabHook::MERGE_REQUEST][GitLabHook::TARGET_BRANCH]) &&
            empty($this->event[GitLabHook::OBJECT_ATTRIBUTES][GitLabHook::TARGET_BRANCH]) &&
            empty($this->event[GitLabHook::OBJECT_ATTRIBUTES][GitLabHook::PIPELINE_BRANCH])
        ) {
            return false;
        }

        if (!empty($this->event[GitLabHook::BRANCH]) && ('refs/heads/' . $this->toBranch != $this->event[GitLabHook::BRANCH])) {
            return false;
        }

        if (!empty($this->event[GitLabHook::MERGE_REQUEST][GitLabHook::TARGET_BRANCH]) && ($this->toBranch != $this->event[GitLabHook::MERGE_REQUEST][GitLabHook::TARGET_BRANCH])) {
            return false;
        }

        if (!empty($this->event[GitLabHook::OBJECT_ATTRIBUTES][GitLabHook::TARGET_BRANCH]) && ($this->toBranch != $this->event[GitLabHook::OBJECT_ATTRIBUTES][GitLabHook::TARGET_BRANCH])) {
            return false;
        }

        if (!empty($this->event[GitLabHook::OBJECT_ATTRIBUTES][GitLabHook::PIPELINE_BRANCH]) && ($this->toBranch != $this->event[GitLabHook::OBJECT_ATTRIBUTES][GitLabHook::PIPELINE_BRANCH])) {
            return false;
        }

        return true;
    }

    /**
     *
     * Checks if the username criterion is matched
     *
     * @return bool
     */
    protected function filterOnUsername()
    {

        if (!$this->username) {
            return true;
        }

        if (empty($this->event[GitLabHook::USERNAME]) && empty($this->event[GitLabHook::USER][GitLabHook::USER_NAME])) {
            return false;
        }

        if (!empty($this->event[GitLabHook::USERNAME]) && ($this->username != $this->event[GitLabHook::USERNAME])) {
            return false;
        }

        if (!empty($this->event[GitLabHook::USER][GitLabHook::USER_NAME]) && ($this->username != $this->event[GitLabHook::USER][GitLabHook::USER_NAME])) {
            return false;
        }

        return true;
    }

}