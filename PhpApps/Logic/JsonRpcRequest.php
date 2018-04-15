<?php
namespace Logic;

class JsonRpcRequest
{
    /** @var string */
    protected $jsonrpc;
    /** @var string */
    protected $method;
    /** @var array */
    protected $params;
    /** @var int */
    protected $id;

    /**
     * JsonRpcRequest constructor.
     * @param string $jsonrpc
     * @param string $method
     * @param array $params
     * @param int $id
     */
    public function __construct(string $jsonrpc, string $method, array $params, int $id)
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
     * @return array
     */
    public function getParams(): array
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