<?php
namespace Model;

use Model\Orm\Column;
use Model\Orm\Object;

/**
 * Class Game
 * @package Model
 *
 * @property int $id
 * @property string $name
 */
class Game extends Object
{
    protected static $tableKey = 'games';

    /**
     * @throws \InvalidArgumentException
     */
    protected function initMapping(): void
    {
        $this->addProperty('name', Column::TYPE_STRING);
        parent::initMapping();
    }

    /**
     * @param string $name
     * @return self
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws Orm\Exceptions\ObjectNotFoundException
     */
    public static function create(string $name): self
    {
        $obj = new self();
        $obj->name = $name;
        $obj->save();

        return $obj;
    }
}