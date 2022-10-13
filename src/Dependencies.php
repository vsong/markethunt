<?php

namespace App;

use DI\Container;

class Dependencies
{
    public static function InjectDependencies(Container $container) {
        $container->set('date', function () {
            return new \DateTime();
        });
    }
}