<?php
namespace Model\Orm\Exceptions;

use Throwable;

class ObjectNotFoundException extends \Exception
{
    /** @var int */
    private $id;
    /** @var string */
    private $model;

    /**
     * ObjectNotFoundException constructor.
     * @param int $id
     * @param string $model
     * @param string $message
     * @param int $code
     * @param Throwable|null $previous
     */
    public function __construct(
        int $id,
        string $model,
        string $message = null,
        int $code = 404,
        Throwable $previous = null)
    {
        if ($message === null) {
            $message = "Object $model($id) not found";
        }
        parent::__construct($message, $code, $previous);
        $this->id = $id;
        $this->model = $model;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getModel(): string
    {
        return $this->model;
    }
}