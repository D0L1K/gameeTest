<?php
$configurator = new Nette\Configurator;
$configurator->enableTracy(__DIR__ . '/Logs');
$configurator->setTimeZone('Europe/Prague');
$configurator->setTempDirectory(__DIR__ . '/Temp');
$configurator->createRobotLoader()
    ->addDirectory(__DIR__)
    ->register();

return $configurator->createContainer();