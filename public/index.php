<?php

use Galstval\GitLabWebhookManager\Handler\GitHandler;
use Galstval\GitLabWebhookManager\Handler\GitLabWebhookManager;
use Galstval\GitLabWebhookManager\Helpers\Config;
use Galstval\GitLabWebhookManager\Helpers\EnvLoader;
use Slim\Factory\AppFactory;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

require __DIR__ . '/../vendor/autoload.php';

$app = AppFactory::create();
$basePath = str_replace($_SERVER["DOCUMENT_ROOT"], "", __DIR__);
$app->setBasePath($basePath);

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware(true, false, false);

$logger = new Logger('webhook');
$logger->pushHandler(new StreamHandler(__DIR__ . '/../logs/app.log', Logger::DEBUG));

try {
    $envLoader = new EnvLoader(__DIR__ . '/../');
    $envLoader->loadEnv();
} catch (\Exception $e) {
    $logger->critical('Environment loading failed: ' . $e->getMessage());
    echo 'Environment loading failed. Check the log for details.';
    exit(1);
}

$config = new Config($envLoader);

try {
    $gitHandler = new GitHandler($config->get('GIT_REPO_PATH'));
} catch (\Exception $e) {
    $logger->critical('GitHandler initialization failed: ' . $e->getMessage());
    echo 'GitHandler initialization failed. Check the log for details.';
    exit(1);
}

$webhookHandler = new GitLabWebhookManager($envLoader, $gitHandler, $logger);

$app->post(
    '/gitlab-webhook/',
    function (Request $request, Response $response) use ($webhookHandler, $logger, $config) {
        $allowedIps = explode(',', $_ENV['ALLOWED_IPS']);
        $clientIp = $request->getServerParams()['REMOTE_ADDR'];

        if (!in_array($clientIp, $allowedIps)) {
            $logger->warning('Unauthorized IP access attempt: ' . $clientIp);
            $response->getBody()->write('Unauthorized IP access attempt: ' . $clientIp);
            return $response->withStatus(403);
        }

        $headers = $request->getHeaders();
        $payload = $request->getParsedBody();

        try {
            $config->validatePayload($payload);
            $webhookHandler->handleWebhook($headers);
            $response->getBody()->write('Webhook processed successfully.');
            return $response->withStatus(200);
        } catch (\Exception $e) {
            $logger->error('Webhook processing failed: ' . $e->getMessage());
            $response->getBody()->write('Webhook processing failed: ' . $e->getMessage());
            return $response->withStatus(500);
        }
    }
);

$app->run();