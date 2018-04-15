<?php
namespace Logic;

class JsonRpcResult
{
    /** @var string|null */
    protected $data;

    /**
     * JsonRpcResult constructor.
     * @param string|null $data
     */
    public function __construct(string $data = null)
    {
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