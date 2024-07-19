<?php

namespace Galstval\GitLabWebhookManager\Helpers;

class Config
{
    private EnvLoader $envLoader;

    /**
     * @param \Galstval\GitLabWebhookManager\Helpers\EnvLoader $envLoader
     */
    public function __construct(EnvLoader $envLoader)
    {
        $this->envLoader = $envLoader;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function get($key)
    {
        return $this->envLoader->get($key);
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function validatePayload(array $payload)
    {
        $this->ensureKeyExists($payload, 'event', 'Event');
        $this->ensureKeyExists($payload, 'state', 'State');
        $this->ensureKeyExists($payload, 'target_branch', 'Target branch');

        $this->validateEvent($payload['event']);
        $this->validateState($payload['state']);
        $this->validateTargetBranch($payload['target_branch']);
    }

    /**
     * @param array $payload
     * @param $key
     * @param $name
     * @return void
     */
    private function ensureKeyExists(array $payload, $key, $name)
    {
        if (!array_key_exists($key, $payload)) {
            throw new \InvalidArgumentException("$name key is missing from the payload.");
        }
    }

    /**
     * @param $event
     * @return void
     */
    private function validateEvent($event)
    {
        if ($event !== $this->get('EVENT')) {
            throw new \InvalidArgumentException("Invalid event: $event. Expected: " . $this->get('EVENT'));
        }
    }

    /**
     * @param $state
     * @return void
     */
    private function validateState($state)
    {
        if ($state !== $this->get('STATE')) {
            throw new \InvalidArgumentException("Invalid state: $state. Expected: " . $this->get('STATE'));
        }
    }

    /**
     * @param $targetBranch
     * @return void
     */
    private function validateTargetBranch($targetBranch)
    {
        if ($targetBranch !== $this->get('TARGET_BRANCH')) {
            throw new \InvalidArgumentException(
                "Invalid target branch: $targetBranch. Allowed branch: " . $this->get('TARGET_BRANCH')
            );
        }
    }
}