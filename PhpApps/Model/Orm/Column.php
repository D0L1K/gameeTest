<?php
namespace Model\Orm;

use Model\Orm\Exceptions\ObjectNotFoundException;

class Column
{
    public const TYPE_INT = 0;
    public const TYPE_STRING = 1;
    public const TYPE_DATE = 2;

    /** @var string */
    private $name;
    /** @var string|int */
    private $type;
    /** @var bool */
    private $isId;
    /** @var bool */
    private $isForeignKey;
    /** @var bool */
    private $isValue;
    /** @var mixed */
    private $value;

    /**
     * Column constructor.
     * @param string $name
     * @param int|string $type
     * @param bool $isId
     * @param bool $isForeignKey
     * @param bool $isValue
     */
    public function __construct(string $name, $type, bool $isValue = true, bool $isForeignKey = false, bool $isId = false)
    {
        $this->name = $name;
        $this->type = $type;
        $this->isId = $isId;
        $this->isForeignKey = $isForeignKey;
        $this->isValue = $isValue;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return int|string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return bool
     */
    public function isId(): bool
    {
        return $this->isId;
    }

    /**
     * @return bool
     */
    public function isForeignKey(): bool
    {
        return $this->isForeignKey;
    }

    /**
     * @return bool
     */
    public function isValue(): bool
    {
        return $this->isValue;
    }

    /**
     * @param mixed $value
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     * @throws \Model\Orm\Exceptions\ObjectNotFoundException
     */
    public function setValue($value): void
    {
        $this->value = $this->processValue($value);
    }

    /**
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param $value
     */
    public function setRawValue($value): void
    {
        $this->value = $value;
    }

    /**
     * @return string|int|null
     */
    public function getRawValue()
    {
        switch (true) {
            case ($this->value instanceof Object):
                return $this->value->getId();
            case ($this->value instanceof \DateTime):
                return $this->value->getTimestamp();
            default:
                /** @var string|int|null $col */
                return $this->value;
        }
    }

    /**
     * @param $value
     * @return int|string|\DateTime|Object
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Model\Orm\Exceptions\ObjectNotFoundException
     */
    private function processValue($value)
    {
        switch (true) {
            case ($value instanceof Object):
                return $value;
            case ($this->type === self::TYPE_INT):
                return $value === null ? null : (int)$value;
            case ($this->type === self::TYPE_STRING):
                return $value === null ? null : (string)$value;
            case ($this->type === self::TYPE_DATE):
                if ($value instanceof \DateTime) {
                    return $value;
                }

                return $value === null ? null : \DateTime::createFromFormat('U.u', $value);
            default:
                // TODO: check if $this->>type is Object
                if (\class_exists($this->type)) {
                    /** @var Object $class */
                    $class = $this->type;

                    return $class::getById($value);
                }
                throw new \InvalidArgumentException("Unknown column '{$this->name}' (type: {$this->type}, value: {$this->value})");
        }
    }
}