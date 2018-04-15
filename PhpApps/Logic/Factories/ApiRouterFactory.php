<?php
namespace Logic\Factories;

use Logic\ApiRouter;
use Nette;

class ApiRouterFactory
{
    use Nette\StaticClass;

    /**
     * @return ApiRouter
     */
    public static function createRouter(): ApiRouter
    {
        return new ApiRouter();
    }
}