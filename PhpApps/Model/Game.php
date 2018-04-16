<?php
namespace Model;

/**
 * Class Game
 * @package Model
 *
 * @var int $id
 * @var string $name
 */
class Game extends Model
{
    /**
     * @throws \InvalidArgumentException
     */
    protected function initMapping(): void
    {
        $this->setTableKey('games');
        $this->addProperty('name', Model::TYPE_STRING);
        parent::initMapping();
    }
}