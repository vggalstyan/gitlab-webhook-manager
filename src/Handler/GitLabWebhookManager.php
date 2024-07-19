<?php

namespace Galstval\GitLabWebhookManager\Handler;

use Galstval\GitLabWebhookManager\Helpers\EnvLoader;
use Psr\Log\LoggerInterface;

class GitLabWebhookManager
{
    protected EnvLoader $envLoader;
    protected GitHandler $gitHandler;
    protected LoggerInterface $logger;

    /**
     * @param \Galstval\GitLabWebhookManager\Helpers\EnvLoader $envLoader
     * @param \Galstval\GitLabWebhookManager\Handler\GitHandler $gitHandler
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(EnvLoader $envLoader, GitHandler $gitHandler, LoggerInterface $logger)
    {
        $this->envLoader = $envLoader;
        $this->gitHandler = $gitHandler;
        $this->logger = $logger;
    }

    /**
     * @throws \Exception
     */
    public function handleWebhook(array $headers)
    {
        try {
            $this->validateToken($headers['X-Gitlab-Token'][0]);

            $this->gitHandler->fetch();
            $this->gitHandler->checkout('origin/' . $this->envLoader->get("TARGET_BRANCH"));
            $this->gitHandler->pull('origin', $this->envLoader->get("TARGET_BRANCH"));

            $this->logger->info('Webhook processed successfully.');

            return true;
        } catch (\Exception $e) {
            $this->logger->error('Webhook processing failed: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * @param $token
     * @return void
     * @throws \Exception
     */
    protected function validateToken($token): void
    {
        $expectedToken = $this->envLoader->get('GITLAB_TOKEN');

        if (empty($token) || empty($expectedToken) || $token !== $expectedToken) {
            throw new \Exception('Invalid GitLab webhook token.');
        }
    }
}