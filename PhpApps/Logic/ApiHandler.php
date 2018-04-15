<?php
namespace Logic;

use Nette\DI\Container;

class ApiHandler
{
    /** @var Container  */
    private $container;

    /**
     * ApiHandler constructor.
     * @param Container $container
     */
    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    /**
     * @param string $class
     * @param string $method
     * @param array $params
     * @return mixed
     */
    public function handle(string $class, string $method, array $params = null)
    {
        $classObj = $this->container->createInstance($class);

        return $classObj->$method($params);
    }
}