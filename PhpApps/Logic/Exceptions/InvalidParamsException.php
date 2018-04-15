<?php
namespace Logic\Exceptions;

class InvalidParamsException extends JsonRpcException
{
    /**
     * InvalidParamsException constructor.
     * @param string|null $data
     */
    public function __construct(string $data = null)
    {
        parent::__construct(-32602, 'Invalid Params', $data);
    }
}