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
    /** @var string|null */
    protected $tableKey;
    /** @var string|null */
    private $idColumn;
    /** @var string|null */
    private $fieldColumn;
    /** @var string|null */
    private $valueColumn;
    /** @var bool */
    private $loading = false;

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

        return $this->getRawColumnValue($columnName);
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
     * @return string
     * @throws \RuntimeException
     */
    protected function getTableKey(): string
    {
        $class = static::class;
        if ($this->tableKey === null) {
            throw new \RuntimeException("Table key is not set for object $class");
        }

        return $this->tableKey;
    }

    /**
     * @param int $id
     * @return string
     * @throws \RuntimeException
     */
    protected function getHashKey(int $id): string
    {
        return $this->getTableKey() . '_' . $id;
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
     * @throws \Exception
     */
    public function __set(string $name, $value = null)
    {
        if ($this->$name === $value) {
            return;
        }
        $this->vars[$name] = $value;
        if (!$this->loading) {
            $this->save(true);
        }
    }

    /**
     * @param string $name
     * @return mixed
     * @throws \Exception
     */
    public function __get(string $name)
    {
        $class = static::class;
        if (!isset($this->$name)) {
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

    /**
     * @param bool $update
     * @throws \RuntimeException
     */
    public function save(bool $update = false): void
    {
        $id = $this->getId();
        if ($id === null) {
            $id = $this->generateId();
        }

        $fieldName = $this->getFieldColumnName();
        $valueName = $this->getValueColumnName();

        $result = $this->getDbClient()->hSet(
            $this->getHashKey($id), $this->getRawColumnValue($fieldName), $this->getRawColumnValue($valueName));
        $class = static::class;
        if ($result === 1 && $update) {
            throw new \RuntimeException(
                "Object $class($id) was updated, however DB says it was inserted as new. DB is probably corrupted");
        }
        if ($result === 0 && !$update) {
            throw new \RuntimeException(
                "Object $class($id) was inserted as new, however DB says it was updated. DB is probably corrupted");
        }
    }

    /**
     * @return int
     * @throws \RuntimeException
     */
    private function generateId(): int
    {
        return $this->getSession()->generateNextId($this->getTableKey());
    }

    /**
     * @param int|null $id
     * @return static|null
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws ObjectNotFoundException
     */
    public function load(int $id = null): ?self
    {
        $this->loading = true;
        $id = $id ?? $this->getId();

        if ($id === null) {
            throw new \RuntimeException('ID value not found');
        }

        $data = $this->getDbClient()->hGetAll($this->getHashKey($id));
        if (\count($data) === 0) {
            return null;
        }
        $idColumn = $this->getIdColumnName();
        $data[$idColumn] = $id;

        $this->processData($data);
        $this->loading = false;

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
     * @param string $colName
     * @return string|int|null
     */
    private function getRawColumnValue(string $colName)
    {
        if (!isset($this->$colName)) {
            $class = static::class;
            throw new \RuntimeException("Unknown column '$colName' provided to object '$class' ");
        }
        $col = $this->$colName;
        $type = $this->varsToType[$colName];
        switch (true) {
            case (class_exists($type)):
                /** @var Object $col */
                return $col->getId();
            case ($type === self::TYPE_DATE):
                /** @var \DateTime $col */
                return $col->getTimestamp();
            default:
                /** @var string|int|null */
                return $col;
        }
    }

    /**
     * @return \Kdyby\Redis\RedisClient
     * @throws \RuntimeException
     */
    protected function getDbClient(): \Kdyby\Redis\RedisClient
    {
        $session = $this->getSession();

        return $session->getClient();
    }

    /**
     * @return Session
     * @throws \RuntimeException
     */
    protected function getSession(): Session
    {
        return Session::getCurrent();
    }
}