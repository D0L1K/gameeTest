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

    protected function initMapping(): void
    {
        parent::initMapping();
        $this->setTableKey('players');
        $this->addProperty('name', Model::TYPE_STRING);
    }
}