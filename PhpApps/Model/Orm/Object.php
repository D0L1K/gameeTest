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
    /** @var Column|null */
    private $idValueColumn;
    /** @var Column|null */
    private $foreignKeyColumn;
    /** @var string|null */
    private $genIdColumnName;
    /** @var string|null */
    private $externalIdColumnName;
    /** @var bool */
    private $genId = true;
    /** @var bool */
    private $initializing;

    /** @var array */
    private static $loadedObjects = [];
    /** @var string|null */
    protected static $tableKey;

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
        $this->initializing = true;
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
        if ($this->idValueColumn === null) {
            $this->addProperty(self::DEFAULT_ID_COL, Column::TYPE_INT, false, false, true);
        }
        if ($this->genId && $this->genIdColumnName === null) {
            $this->setGenIdColumnName(self::DEFAULT_ID_COL);
        }
    }

    ////////////////////
    // Properties     //
    ////////////////////

    /**
     * @param string $name
     * @param string|int $type
     * @param bool $isForeignKey
     * @param bool $valueInId
     * @param bool $isValue
     * @throws \InvalidArgumentException
     */
    protected function addProperty(
        string $name, $type, bool $isValue = true, bool $isForeignKey = false, bool $valueInId = false): void
    {
        $column = new Column($name, $type, $isValue, $isForeignKey, $valueInId);
        $this->columns[$name] = $column;
        if ($valueInId) {
            $this->addIdValueColumn($column);

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
    private function addIdValueColumn(Column $column): void
    {
        if ($this->idValueColumn !== null) {
            throw new \InvalidArgumentException(
                "ID column is already set. Actual: {$this->idValueColumn->getName()} Added: {$column->getName()}");
        }
        $type = $column->getType();
        if ($type !== Column::TYPE_INT && !\is_string($type)) {
            throw new \InvalidArgumentException('ID column has to be int or Model object');
        }
        $this->idValueColumn = $column;
    }

    /**
     * @param Column $column
     * @throws \InvalidArgumentException
     */
    private function addForeignKeyColumn(Column $column): void
    {
        if ($this->foreignKeyColumn !== null) {
            throw new \InvalidArgumentException(
                "Foreign key column is already set. Actual: {$this->idValueColumn->getName()} Added: {$column->getName()}");
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
     * Disables automatic generating of ID
     *
     * @return void
     */
    protected function setNoGenId(): void
    {
        $this->genId = false;
    }

    /**
     * @param string $columnName
     */
    protected function setGenIdColumnName(string $columnName): void
    {
        if (!isset($this->$columnName)) {
            throw new \InvalidArgumentException("Unknown column ($columnName) provided to be generated as ID");
        }
        $this->genIdColumnName = $columnName;
    }

    /**
     * @return bool
     */
    protected function foreignKeyColExist(): bool
    {
        return $this->foreignKeyColumn !== null;
    }

    /**
     * @param string $columnName
     */
    protected function setExternalId(string $columnName): void
    {
        if (!isset($this->$columnName)) {
            throw new \InvalidArgumentException("Unknown column ($columnName) provided as external ID");
        }
        $this->externalIdColumnName = $columnName;
    }

    /**
     * @return bool
     */
    protected function isExternalId(): bool
    {
        return $this->externalIdColumnName !== null;
    }

    /**
     * @return int
     */
    protected function getExternalId(): int
    {
        $colName = $this->externalIdColumnName;

        return $this->$colName;
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
        if (!$this->initializing) {
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
        $idValue = $this->idValueColumn->getValue();
        if (!($idValue instanceof self)) {
            return $this->idValueColumn->getRawValue();
        }

        return $idValue->isExternalId() ? $idValue->getExternalId() : $idValue->getId();
    }

    /**
     * @param int $id
     * @return string
     * @throws \RuntimeException
     */
    public static function getHashKey(int $id): string
    {
        return static::getTableKey() . '_' . $id;
    }

    /**
     * @return null|string
     */
    public static function getTableKey(): ?string
    {
        if (static::$tableKey === null) {
            throw new \RuntimeException('Table key is not set for ' . static::class);
        }

        return static::$tableKey;
    }

    ////////////////////
    // Save & Load    //
    ////////////////////

    /**
     * @param bool $isUpdate
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws ObjectNotFoundException
     */
    public function save(bool $isUpdate = false): void
    {
        $genIdColName = $this->genIdColumnName;
        if ($this->genId && $genIdColName !== null && $this->$genIdColName === null) {
            $this->$genIdColName = $this->generateId();
        }
        $id = $this->getId();

        if ($this->foreignKeyColExist()) {
            // ValueColumn should be everytime only one in this case
            foreach ($this->valueColumns as $column) {
                $this->executeSave($id, $this->foreignKeyColumn->getRawValue(), $column->getRawValue(), $isUpdate);
            }
        } else {
            foreach ($this->valueColumns as $column) {
                $this->executeSave($id, $column->getName(), $column->getRawValue(), $isUpdate);
            }
        }
        if (!$isUpdate) {
            $this->idValueColumn->setValue($id);
            $this->initializing = false;
        }
    }

    /**
     * @param int $id
     * @param $fieldName
     * @param $fieldValue
     * @param bool $isUpdate
     * @throws \RuntimeException
     */
    private function executeSave(int $id, $fieldName, $fieldValue, bool $isUpdate): void
    {
        $result = $this->getDbClient()->hSet(static::getHashKey($id), $fieldName, $fieldValue);
        $this->checkResult($result, $isUpdate, $id);
    }

    /**
     * @param int $result
     * @param bool $isUpdate
     * @param int $id
     * @throws \RuntimeException
     */
    private function checkResult(int $result, bool $isUpdate, int $id): void
    {
        $class = static::class;
        if ($result === 1 && $isUpdate) {
            throw new \RuntimeException(
                "Object $class($id) was updated, however DB says it was inserted as new. DB is probably corrupted");
        }
        if ($result === 0 && !$isUpdate) {
            throw new \RuntimeException(
                "Object $class($id) was inserted as new, however DB says it was updated. You are inserting already existing values");
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
        $id = $id ?? $this->getId();

        if ($id === null) {
            throw new \RuntimeException('ID value not found');
        }

        if ($this->foreignKeyColExist()) {
            if ($foreignKeyId === null) {
                throw new \RuntimeException('Foreign key ID must be provided');
            }
            $this->foreignKeyColumn->setValue($foreignKeyId);
            $data = $this->getDbClient()->hGet(static::getHashKey($id), $this->foreignKeyColumn->getRawValue());
            // ValueColumn should be everytime only one in this case
            foreach ($this->valueColumns as $valueColumn) {
                $valueColumn->setValue($data);
            }
        } else {
            $data = $this->getDbClient()->hGetAll(static::getHashKey($id));
            foreach ($data as $name => $value) {
                if (isset($this->$name)) {
                    $this->$name = $value;
                }
            }
        }

        if (\count($data) === 0) {
            return null;
        }
        $this->idValueColumn->setValue($id);


        $this->initializing = false;

        return $this;
    }

    /**
     * @return int
     * @throws \RuntimeException
     */
    private function generateId(): int
    {
        return $this->getSession()->generateNextId(static::getTableKey());
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