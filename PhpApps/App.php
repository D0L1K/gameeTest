<?php

use Nette\DI\Container;

class App
{
    /** @var Container */
    private $container;
    /** @var bool */
    private $inited;

    /**
     * @return void
     */
    public function run(): void
    {
        $this->init();
        $this->container->getByType(Nette\Application\Application::class)
            ->run();
    }

    /**
     * @return void
     */
    private function init(): void
    {
        if ($this->inited) {
            return;
        }

        $configurator = new Nette\Configurator;
        $configurator->enableTracy(__DIR__ . '/Logs');
        $configurator->setTimeZone('Europe/Prague');
        $configurator->setTempDirectory(__DIR__ . '/Temp');
        $configurator->addConfig(__DIR__ . '/Conf/config.neon');
        $configurator->createRobotLoader()
            ->addDirectory(__DIR__)
            ->register();

        $this->container = $configurator->createContainer();

        $this->inited = true;
    }
}
