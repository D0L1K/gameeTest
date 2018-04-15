<?php
namespace Logic\Exceptions;

class JsonRpcException extends \Exception
{
    /** @var string|null */
    protected $data;

    /**
     * JsonRpcException constructor.
     * @param int $code
     * @param string $message
     * @param string|null $data
     */
    public function __construct(int $code, string $message, string $data = null)
    {
        parent::__construct($message, $code);
        $this->data = $data;
    }

    /**
     * @return null|string
     */
    public function getData(): ?string
    {
        return $this->data;
    }
}