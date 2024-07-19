<?php

namespace Galstval\GitLabWebhookManager\Handler;

use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class GitHandler
{
    protected string $repositoryPath;

    /**
     * @param $repositoryPath
     */
    public function __construct($repositoryPath)
    {
        $this->repositoryPath = $repositoryPath;
        $this->checkGitRepositoryExists();
    }

    /**
     * @return void
     */
    public function fetch()
    {
        $this->runCommand(['git', 'fetch', '--all']);
    }

    /**
     * @param $branch
     * @return void
     */
    public function checkout($branch)
    {
        $this->runCommand(['git', 'checkout', '--force', $branch]);
    }

    /**
     * @param $remote
     * @param $branch
     * @return void
     */
    public function pull($remote, $branch)
    {
        $this->runCommand(['git', 'pull', $remote, $branch]);
    }

    /**
     * @return void
     */
    protected function checkGitRepositoryExists()
    {
        $this->runCommand(['git', 'rev-parse', '--is-inside-work-tree']);
    }

    /**
     * @param array $command
     * @return void
     */
    protected function runCommand(array $command)
    {
        $process = new Process($command, $this->repositoryPath);
        $process->mustRun();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }
    }
}