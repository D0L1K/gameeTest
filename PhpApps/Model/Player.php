<?php
namespace Model;

use Model\Orm\Column;
use Model\Orm\Object;

/**
 * Class Player
 * @package Model
 *
 * @var int $id
 * @var string $name
 */
class Player extends Object
{
    /**
     * @throws \InvalidArgumentException
     */
    protected function initMapping(): void
    {
        $this->setTableKey('players');
        $this->addProperty('name', Column::TYPE_STRING);
        $this->addProperty('city', Column::TYPE_STRING);
        parent::initMapping();
    }
}