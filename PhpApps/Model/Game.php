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
    protected function initMapping(): void
    {
        parent::initMapping();
        $this->setTableKey('games');
        $this->addProperty('name', Model::TYPE_STRING);
    }
}