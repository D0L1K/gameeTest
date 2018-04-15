<?php
namespace Logic;

class JsonRpcRequest
{
    /** @var string */
    protected $jsonrpc;
    /** @var string */
    protected $method;
    /** @var \stdClass|null */
    protected $params;
    /** @var int */
    protected $id;

    /**
     * JsonRpcRequest constructor.
     * @param string $jsonrpc
     * @param string $method
     * @param int $id
     * @param \stdClass|null $params
     */
    public function __construct(string $jsonrpc, string $method, int $id, \stdClass $params = null)
    {
        $this->jsonrpc = $jsonrpc;
        $this->method = $method;
        $this->params = $params;
        $this->id = $id;
    }

    /**
     * @return string
     */
    public function getJsonrpc(): string
    {
        return $this->jsonrpc;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return \stdClass|null
     */
    public function getParams(): ?\stdClass
    {
        return $this->params;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }
}