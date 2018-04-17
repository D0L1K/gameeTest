<?php
namespace Model;

use Model\Orm\Column;
use Model\Orm\Object;

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
        $this->addProperty('name', Column::TYPE_STRING);
        parent::initMapping();
    }
}