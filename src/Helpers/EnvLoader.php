<?php

namespace Galstval\GitLabWebhookManager\Helpers;

use Dotenv\Dotenv;

class EnvLoader
{
    protected Dotenv $dotenv;

    /**
     * @param string|null $pathToEnv
     */
    public function __construct(string $pathToEnv = null)
    {
        if ($pathToEnv === null) {
            $pathToEnv = __DIR__ . '/../../';
        }

        $this->dotenv = Dotenv::createMutable($pathToEnv);
    }

    /**
     * @return void
     * @throws \Exception
     */
    public function loadEnv()
    {
        try {
            $this->dotenv->load();
            $this->dotenv->required(['GITLAB_TOKEN', "GIT_REPO_PATH", "ALLOWED_IPS", "EVENT", "TARGET_BRANCH", "STATE"])->notEmpty();
        } catch (\Throwable $e) {
            throw new \Exception('Failed to load .env file: ' . $e->getMessage());
        }
    }

    /**
     * @param string $key
     * @return mixed
     */
    public function get(string $key)
    {
        return $_ENV[$key];
    }
}