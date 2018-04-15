<?php
namespace Logic;

use Logic\Exceptions\JsonRpcException;
use Nette\Application\Responses\JsonResponse;
use Nette\Http\IResponse;

class JsonRpcResponse implements \JsonSerializable
{
    /** @var string */
    protected $jsonrpc;
    /** @var JsonRpcResult|null */
    protected $result;
    /** @var JsonRpcException|null */
    protected $error;
    /** @var int|null */
    protected $id;
    /** @var int */
    protected $code;

    /**
     * JsonRpcRequest constructor.
     * @param string $jsonrpc
     */
    public function __construct(string $jsonrpc = '2.0')
    {
        $this->jsonrpc = $jsonrpc;
    }

    /**
     * @return string
     */
    public function getJsonrpc(): string
    {
        return $this->jsonrpc;
    }

    /**
     * @return JsonRpcResult|null
     */
    public function getResult(): ?JsonRpcResult
    {
        return $this->result;
    }

    /**
     * @return JsonRpcException|null
     */
    public function getError(): ?JsonRpcException
    {
        return $this->error;
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @param int $id
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * @param JsonRpcResult $result
     */
    public function setResult($result): void
    {
        $this->result = $result;
        $this->setCode(IResponse::S200_OK);
    }

    /**
     * @param JsonRpcException $error
     */
    public function setError(JsonRpcException $error): void
    {
        $this->error = $error;
        $this->setCode($error->getCode());
    }

    /**
     * @return bool
     */
    public function isSuccess(): bool
    {
        return $this->error === null;
    }

    /**
     * @return int
     */
    public function getCode(): int
    {
        return $this->code;
    }

    /**
     * @param int $code
     */
    public function setCode(int $code): void
    {
        $this->code = $code;
    }

    /**
     * @return JsonResponse
     */
    public function getHttpResponse(): JsonResponse
    {
        $data = json_encode($this);

        return new JsonResponse($data, 'application/json');
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
        $return['jsonrpc'] = $this->jsonrpc;
        if ($this->isSuccess()) {
            $return['result'] = $this->result;
        } else {
            $return['error'] = $this->error;
        }
        $return['id'] = $this->id;

        return $return;
    }
}