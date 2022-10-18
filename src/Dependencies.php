<?php

namespace App;

use App\Database\DatabaseConnectionFactory;
use App\DataService\MarketInfoQueryService;
use DateTime;
use DI\Container;

class Dependencies
{
    public static function InjectDependencies(Container $container) {
        $container->set('date', fn() => new DateTime());
        $container->set('db', fn() => DatabaseConnectionFactory::Create($_ENV['DB_DSN'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']));
        $container->set('marketInfoQueryService', fn() => new MarketInfoQueryService($container->get('db')));
    }
}