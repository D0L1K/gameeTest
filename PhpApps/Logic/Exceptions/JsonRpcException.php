<?php
namespace Logic\Exceptions;

class JsonRpcException extends \Exception implements \JsonSerializable
{
    /** @var string|null */
    protected $data;
    /** @var int*/
    protected $rpcCode;

    /**
     * JsonRpcException constructor.
     * @param int $httpCode
     * @param string $message
     * @param int $rpcCode
     * @param string|null $data
     */
    public function __construct(int $httpCode, string $message, int $rpcCode = -32603, string $data = null)
    {
        parent::__construct($message, $httpCode);
        $this->data = $data;
        $this->rpcCode = $rpcCode;
    }

    /**
     * @return null|string
     */
    public function getData(): ?string
    {
        return $this->data;
    }

    /**
     * @return int
     */
    public function getRpcCode(): int
    {
        return $this->rpcCode;
    }

    /**
     * Specify data which should be serialized to JSON
     * @link http://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4.0
     */
    public function jsonSerialize()
    {
        return ['code' => $this->rpcCode, 'message' => $this->message, 'data' => $this->data];
    }
}