<?php
namespace Logic\Factories;

use Nette;
use Nette\Application\Routers\Route;
use Nette\Application\Routers\RouteList;

class RouterFactory
{
    use Nette\StaticClass;

    /**
     * @return Nette\Application\IRouter
     */
    public static function createRouter(): \Nette\Application\IRouter
    {
        $router = new RouteList;
        $router[] = new Route('/api[/<endpoint>]', 'Api:default');
        $router[] = new Route('/', 'Default:default');

        return $router;
    }
}