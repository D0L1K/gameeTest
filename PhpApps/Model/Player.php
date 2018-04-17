<?php
namespace Model;

use Model\Orm\Column;
use Model\Orm\Object;

/**
 * Class Player
 * @package Model
 *
 * @property string $name
 * @property string $city
 */
class Player extends Object
{
    protected static $tableKey = 'players';

    /**
     * @throws \InvalidArgumentException
     */
    protected function initMapping(): void
    {
        $this->addProperty('name', Column::TYPE_STRING);
        $this->addProperty('city', Column::TYPE_STRING);
        parent::initMapping();
    }

    /**
     * @param string $name
     * @param string|null $city
     * @return self
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws Orm\Exceptions\ObjectNotFoundException
     */
    public static function create(string $name, string $city = null): self
    {
        $obj = new self();
        $obj->name = $name;
        $obj->city = $city;
        $obj->save();

        return $obj;
    }
}