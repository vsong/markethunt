<?php

use App\Dependencies;
use App\Routes;
use DI\Container;
use Slim\Factory\AppFactory;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../config');
$dotenv->safeLoad();

$container = new Container();
Dependencies::InjectDependencies($container);
AppFactory::setContainer($container);

$app = AppFactory::create();
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(false, true, true);
Routes::AddRoutes($app);

$app->run();