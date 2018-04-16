<?php
namespace Model;

/**
 * Class Game
 * @package Model
 *
 * @var int $id
 * @var string $name
 */
class Game extends Object
{
    /**
     * @throws \InvalidArgumentException
     */
    protected function initMapping(): void
    {
        $this->setTableKey('games');
        $this->addProperty('name', Object::TYPE_STRING);
        parent::initMapping();
    }
}