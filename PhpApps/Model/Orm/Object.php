<?php
namespace Model\Orm;

use Model\Orm\Exceptions\ObjectNotFoundException;
use Logic\Session;

/**
 * Class Object
 * @package Model
 */
class Object
{
    private const DEFAULT_ID_COL = 'id';

    /** @var Column[] */
    protected $columns;
    /** @var Column[] */
    protected $valueColumns = [];
    /** @var string|null */
    protected $tableKey;
    /** @var Column|null */
    private $idColumn;
    /** @var Column|null */
    private $foreignKeyColumn;
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
        if ($object->foreignKeyColExist()) {
            throw new \RuntimeException(
                'Cannot use getById() because ' . static::class . ' has field name as foreign key column');
        }
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
     * @param int $id
     * @param int $foreignKeyId
     * @param bool $throwIfNull
     * @return static
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws ObjectNotFoundException
     */
    public static function getByIdAndFkId(int $id, int $foreignKeyId, bool $throwIfNull = true): self
    {
        if (isset(self::$loadedObjects[static::class][$id][$foreignKeyId])) {
            return self::$loadedObjects[static::class][$id][$foreignKeyId];
        }
        $object = new static();
        if (!$object->foreignKeyColExist()) {
            throw new \RuntimeException(
                'Cannot use getById() because ' . static::class . ' has field name as foreign key column');
        }
        $object = $object->load($id, $foreignKeyId);
        if ($object !== null) {
            return self::$loadedObjects[static::class][$id][$foreignKeyId] = $object;
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

    ////////////////////
    // Mapping        //
    ////////////////////

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
        if ($this->idColumn === null) {
            $this->addProperty(self::DEFAULT_ID_COL, Column::TYPE_INT, false, false, true);
        }
    }

    ////////////////////
    // Properties     //
    ////////////////////

    /**
     * @param string $name
     * @param string|int $type
     * @param bool $isForeignKey
     * @param bool $isId
     * @param bool $isValue
     * @throws \InvalidArgumentException
     */
    protected function addProperty(
        string $name, $type, bool $isValue = true, bool $isForeignKey = false, bool $isId = false): void
    {
        $column = new Column($name, $type, $isValue, $isForeignKey, $isId);
        $this->columns[$name] = $column;
        if ($isId) {
            $this->addIdColumn($column);

            return;
        }
        if ($isForeignKey) {
            $this->addForeignKeyColumn($column);

            return;
        }
        $this->addValueColumn($column);
    }

    /**
     * @param Column $column
     * @throws \InvalidArgumentException
     */
    private function addIdColumn(Column $column): void
    {
        if ($this->idColumn !== null) {
            throw new \InvalidArgumentException(
                "ID column is already set. Actual: {$this->idColumn->getName()} Added: {$column->getName()}");
        }
        $type = $column->getType();
        if ($type !== Column::TYPE_INT && !\is_string($type)) {
            throw new \InvalidArgumentException('ID column has to be int or Model object');
        }
        $this->idColumn = $column;
    }

    /**
     * @param Column $column
     * @throws \InvalidArgumentException
     */
    private function addForeignKeyColumn(Column $column): void
    {
        if ($this->foreignKeyColumn !== null) {
            throw new \InvalidArgumentException(
                "Foreign key column is already set. Actual: {$this->idColumn->getName()} Added: {$column->getName()}");
        }
        if (\count($this->valueColumns) > 1) {
            throw new \InvalidArgumentException(
                'Cannot create ForeignKeyColumn as there is more than 1 ValueColumn specified');
        }
        $this->foreignKeyColumn = $column;
    }

    /**
     * @param Column $column
     * @throws \InvalidArgumentException
     */
    private function addValueColumn(Column $column): void
    {
        if ($this->foreignKeyColumn !== null && \count($this->valueColumns) !== 0) {
            throw new \InvalidArgumentException(
                'Cannot specify more than 1 ValueColumn in case ForeignKeyColumn is specified');
        }
        $this->valueColumns[$column->getName()] = $column;
    }

    /**
     * @return bool
     */
    protected function foreignKeyColExist(): bool
    {
        return $this->foreignKeyColumn !== null;
    }

    ////////////////////
    // Magic methods  //
    ////////////////////

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
        $this->columns[$name]->setValue($value);
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

        return $this->columns[$name]->getValue();
    }

    /**
     * @param string $name
     * @return bool
     */
    public function __isset(string $name): bool
    {
        return array_key_exists($name, $this->columns);
    }

    ////////////////////
    // Gets & Sets    //
    ////////////////////

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->idColumn->getRawValue();
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
     * @param string $tableKey
     */
    protected function setTableKey(string $tableKey): void
    {
        $this->tableKey = $tableKey;
    }

    /**
     * @return string
     */
    protected function getTableKey(): string
    {
        if ($this->tableKey === null) {
            throw new \RuntimeException('Table key is not set for ' . static::class);
        }

        return $this->tableKey;
    }

    ////////////////////
    // Save & Load    //
    ////////////////////

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
     * @param int|null $id
     * @param int|null $foreignKeyId
     * @return static|null
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \Model\Orm\Exceptions\ObjectNotFoundException
     */
    public function load(int $id = null, int $foreignKeyId = null): ?self
    {
        $this->loading = true;
        $id = $id ?? $this->getId();

        if ($id === null) {
            throw new \RuntimeException('ID value not found');
        }

        if ($this->foreignKeyColumn !== null) {
            if ($foreignKeyId === null) {
                throw new \RuntimeException('Foreign key ID must be provided');
            }
            $this->foreignKeyColumn->setValue($foreignKeyId);
            $data = $this->getDbClient()->hGet($this->getHashKey($id), $this->foreignKeyColumn->getRawValue());
            // value column should be everytime only one in this case
            foreach ($this->valueColumns as $valueColumn) {
                $valueColumn->setValue($data);
            }
        } else {
            $data = $this->getDbClient()->hGetAll($this->getHashKey($id));
            foreach ($data as $name => $value) {
                if (isset($this->$name)) {
                    $this->$name = $value;
                }
            }
        }

        if (\count($data) === 0) {
            return null;
        }
        $this->idColumn->setValue($id);


        $this->loading = false;

        return $this;
    }

    /**
     * @return int
     * @throws \RuntimeException
     */
    private function generateId(): int
    {
        return $this->getSession()->generateNextId($this->getTableKey());
    }

    ////////////////////
    // Helper methods //
    ////////////////////

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