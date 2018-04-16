<?php
namespace Model;

/**
 * Class Player
 * @package Model
 *
 * @var int $id
 * @var string $name
 */
class Player extends Model
{
    /**
     * @throws \InvalidArgumentException
     */
    protected function initMapping(): void
    {
        $this->setTableKey('players');
        $this->addProperty('name', Model::TYPE_STRING);
        parent::initMapping();
    }
}