<?php

namespace App;

use App\Database\DatabaseConnectionFactory;
use App\DataService\ApiTokenQueryService;
use App\DataService\MarketAnalyticsQueryService;
use App\DataService\MarketInfoQueryService;
use App\DataService\OtcQueryService;
use DI\Container;

class Dependencies
{
    public static function InjectDependencies(Container $container) {
        $container->set('db', fn() => DatabaseConnectionFactory::Create($_ENV['DB_DSN'], $_ENV['DB_USER'], $_ENV['DB_PASSWORD']));
        $container->set('marketInfoQueryService', fn() => new MarketInfoQueryService($container->get('db')));
        $container->set('marketAnalyticsQueryService', fn() => new MarketAnalyticsQueryService($container->get('db')));
        $container->set('otcQueryService', fn() => new OtcQueryService($container->get('db')));
        $container->set('apiTokenQueryService', fn() => new ApiTokenQueryService([$_ENV['RESTRICTED_ENDPOINTS_TOKEN']]));
    }
}