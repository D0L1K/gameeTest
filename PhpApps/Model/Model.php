<?php
namespace Model;
use Logic\Session;

/**
 * Class AbstractModel
 * @package Model
 *
 * @var int $id
 */
class Model
{
    protected const TYPE_INT = 0;
    protected const TYPE_STRING = 1;
    protected const TYPE_DATE = 2;

    /** @var array  */
    protected $vars = [];
    /** @var array */
    protected $varsToType = [];
    /** @var array  */
    protected $varsToCol = [];
    /** @var bool */
    protected $save = false;
    /** @var string|null */
    protected $tableKey;

    public function __construct()
    {
        $this->initMapping();
    }

    protected function initMapping(): void
    {
        $this->addProperty('id', self::TYPE_INT);
    }

    /**
     * @return int|null
     */
    public function getId(): ?int
    {
        return $this->id;
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
        $class = static::class;
        if ($this->tableKey === null) {
            throw new \RuntimeException("Table key is not set for object $class");
        }

        return $this->tableKey;
    }

    /**
     * @param string $name
     * @param string $type
     * @param string|null $dbCol
     */
    protected function addProperty(string $name, string $type, string $dbCol = null): void
    {
        $this->vars[$name] = null;
        $this->varsToType[$name] = $type;
        $this->varsToCol[$name] = $dbCol ?? $name;
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

    public function load()
    {
        $session = Session::getCurrent();
        $dbClient = $session->getClient();

    }

    /**
     * @return string
     */
    protected function getHashKey(): string
    {
        return $this->getTableKey();
    }

    /**
     * @return string
     */
    protected function getFieldKey(): string
    {
        return $this->getId();
    }
}