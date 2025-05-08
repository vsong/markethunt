<?php

use App\Dependencies;
use App\Routes;
use DI\Container;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../config');
$dotenv->safeLoad();

if ($_ENV['SENTRY_DSN'] !== '') {
    Sentry\init([
        'dsn' => $_ENV['SENTRY_DSN']
    ]);
}

$container = new Container();
Dependencies::InjectDependencies($container);
AppFactory::setContainer($container);

$app = AppFactory::create();
$app->addRoutingMiddleware();

$errorMiddleware = $app->addErrorMiddleware(false, true, true);

// inject Sentry to Slim's default error handler
if ($_ENV['SENTRY_DSN'] !== '') {
    $defaultErrorHandler = $errorMiddleware->getDefaultErrorHandler();

    $customErrorHandler = function (
        Slim\Http\ServerRequest $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ) use ($defaultErrorHandler) {
        if (!($exception instanceof \Slim\Exception\HttpException)) {
            Sentry\captureException($exception);
        }

        return $defaultErrorHandler($request, $exception, $displayErrorDetails, $logErrors, $logErrorDetails);
    };

    $errorMiddleware->setDefaultErrorHandler($customErrorHandler);
}

Routes::AddRoutes($app);

$app->run();