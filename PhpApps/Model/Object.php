<?php
namespace Model;

use Logic\Exceptions\ObjectNotFoundException;
use Logic\Session;

/**
 * Class Object
 * @package Model
 */
class Object
{
    private const DEFAULT_ID_COL = 'id';
    private const DEFAULT_FIELD_COL = 'field';
    private const DEFAULT_VALUE_COL = 'value';

    protected const TYPE_INT = 0;
    protected const TYPE_STRING = 1;
    protected const TYPE_DATE = 2;

    /** @var array  */
    protected $vars = [];
    /** @var array */
    protected $varsToType = [];
    /** @var bool */
    protected $save = false;
    /** @var string|null */
    protected $tableKey;
    /** @var string|null */
    private $idColumn;
    /** @var string|null */
    private $fieldColumn;
    /** @var string|null */
    private $valueColumn;

    /** @var array */
    private static $loadedObjects = [];

    /**
     * @param int $id
     * @param bool $throwIfNull
     * @return static
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws ObjectNotFoundException
     */
    public static function getById(int $id, bool $throwIfNull = true): self
    {
        if (isset(self::$loadedObjects[static::class][$id])) {
            return self::$loadedObjects[static::class][$id];
        }
        $object = new static();
        $object = $object->load($id);
        if ($object !== null) {
            return self::$loadedObjects[static::class][$id] = $object;
        }
        if (!$throwIfNull) {
            return null;
        }
        throw new ObjectNotFoundException($id, static::class);
    }

    /**
     * Object constructor.
     * Do not allow to create objects from New -> protected
     * @throws \InvalidArgumentException
     */
    protected function __construct()
    {
        $this->initMapping();
    }

    /**
     * @throws \InvalidArgumentException
     */
    protected function initMapping(): void
    {
        $this->initDefault();
    }

    /**
     * @throws \InvalidArgumentException
     */
    private function initDefault(): void
    {
        if ($this->getIdColumnName() === self::DEFAULT_ID_COL) {
            $this->addProperty(self::DEFAULT_ID_COL, self::TYPE_INT, false, true);
        }
        if ($this->getFieldColumnName() === self::DEFAULT_FIELD_COL) {
            $this->addProperty(self::DEFAULT_FIELD_COL, self::TYPE_STRING);
        }
        if ($this->getValueColumnName() === self::DEFAULT_VALUE_COL) {
            $this->addProperty(self::DEFAULT_VALUE_COL, self::TYPE_STRING, false, false);
        }
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        $columnName = $this->getIdColumnName();

        return $this->$columnName;
    }

    /**
     * @return null|string
     */
    private function getIdColumnName(): string
    {
        return $this->idColumn ?? self::DEFAULT_ID_COL;
    }

    /**
     * @return null|string
     */
    private function getFieldColumnName(): string
    {
        return $this->fieldColumn ?? self::DEFAULT_FIELD_COL;
    }

    /**
     * @return null|string
     */
    private function getValueColumnName(): string
    {
        return $this->valueColumn ?? self::DEFAULT_VALUE_COL;
    }

    /**
     * @param string $tableKey
     */
    protected function setTableKey(string $tableKey): void
    {
        $this->tableKey = $tableKey;
    }

    /**
     * @param int $id
     * @return string
     * @throws \RuntimeException
     */
    protected function getTableKey(int $id): string
    {
        $class = static::class;
        if ($this->tableKey === null) {
            throw new \RuntimeException("Table key is not set for object $class");
        }

        return $this->tableKey . '_' . $id;
    }

    /**
     * @param string $name
     * @param string|int $type
     * @param bool $isField
     * @param bool $isId
     * @throws \InvalidArgumentException
     */
    protected function addProperty(string $name, $type, bool $isField = true, bool $isId = false): void
    {
        if ($isId) {
            $this->addIdColumn($name, $type);
        }
        if ($isField) {
            $this->addFieldColumn($name);
        }
        if (!$isId && !$isField) {
            $this->addValueColumn($name);
        }
        $this->vars[$name] = null;
        $this->varsToType[$name] = $type;
    }

    /**
     * @param string $name
     * @param string|int $type
     * @throws \InvalidArgumentException
     */
    private function addIdColumn(string $name, $type): void
    {
        if ($this->idColumn !== null) {
            throw new \InvalidArgumentException(
                "ID column is already set. Actual: {$this->idColumn} Added: $name");
        }
        if ($type !== self::TYPE_INT && !\is_string($type)) {
            throw new \InvalidArgumentException('ID column has to be int or Model object');
        }
        $this->idColumn = $name;
    }

    /**
     * @param string $name
     * @throws \InvalidArgumentException
     */
    private function addFieldColumn(string $name): void
    {
        if ($this->fieldColumn !== null) {
            throw new \InvalidArgumentException(
                "Field column is already set. Actual: {$this->fieldColumn} Added: $name");
        }
        $this->fieldColumn = $name;
    }

    /**
     * @param string $name
     * @throws \InvalidArgumentException
     */
    private function addValueColumn(string $name): void
    {
        if ($this->valueColumn !== null) {
            throw new \InvalidArgumentException(
                "Value column is already set. Actual: {$this->valueColumn} Added: $name");
        }
        $this->valueColumn = $name;
    }

    /**
     * @param string $name
     * @param mixed $value
     */
    public function __set(string $name, $value = null)
    {
        $this->vars[$name] = $value;
    }

    /**
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    public function __get(string $name)
    {
        $class = static::class;
        if (!array_key_exists($name, $this->vars)) {
            throw new \RuntimeException("Unknown column '$name' in object '$class'");
        }

        return $this->vars[$name];
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return array_key_exists($name, $this->vars);
    }

    public function save()
    {

    }

    /**
     * @param int|null $id
     * @return self|null
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws ObjectNotFoundException
     */
    public function load(int $id = null): ?self
    {
        $id = $id ?? $this->getId();

        if ($id === null) {
            throw new \RuntimeException('ID value not found');
        }

        $data = $this->getDbClient()->hGetAll($this->getTableKey($id));
        if ($data === null) {
            return null;
        }
        $idColumn = $this->getIdColumnName();
        $data[$idColumn] = $id;

        $this->processData($data);

        return $this;
    }

    /**
     * @param array $data
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws ObjectNotFoundException
     */
    private function processData(array $data): void
    {
        foreach ($data as $key => $value) {
            if (isset($this->$key)) {
                $this->$key = $this->processValue($key, $value);
            } else {
                $fieldColumn = $this->getFieldColumnName();
                $this->$fieldColumn = $this->processValue($fieldColumn, $key);
                $valueColumn = $this->getValueColumnName();
                $this->$valueColumn = $this->processValue($valueColumn, $value);
            }
        }
    }

    /**
     * @param string $key
     * @param $value
     * @return mixed
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws ObjectNotFoundException
     */
    private function processValue(string $key, $value)
    {
        $type = $this->varsToType[$key];
        switch (true) {
            case ($type ===self::TYPE_INT):
                return $value === null ? null : (int)$value;
            case ($type ===self::TYPE_STRING):
                return $value === null ? null : (string)$value;
            case ($type ===self::TYPE_DATE):
                return $value === null ? null : (new \DateTime)->setTimestamp($value);
            default:
                if (class_exists($type)) {
                    return $type::getById($value);
                }
                throw new \InvalidArgumentException("Unknown column '$key' (type: $type, value: $value)");
        }
    }

    /**
     * @return \Kdyby\Redis\RedisClient
     * @throws \RuntimeException
     */
    private function getDbClient(): \Kdyby\Redis\RedisClient
    {
        $session = Session::getCurrent();

        return $session->getClient();
    }

}