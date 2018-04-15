<?php
namespace Logic\Exceptions;

use Nette\Http\IResponse;

class InvalidParamsException extends JsonRpcException
{
    /**
     * InvalidParamsException constructor.
     * @param string|null $data
     */
    public function __construct(string $data = null)
    {
        parent::__construct(Iresponse::S400_BAD_REQUEST, 'Invalid Params', -32602, $data);
    }
}