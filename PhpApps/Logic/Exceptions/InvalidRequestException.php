<?php
namespace Logic\Exceptions;

use Nette\Http\IResponse;

class InvalidRequestException extends JsonRpcException
{
    /**
     * InvalidRequestException constructor.
     * @param string|null $data
     */
    public function __construct(string $data = null)
    {
        parent::__construct(Iresponse::S400_BAD_REQUEST, 'Invalid Request', -32600, $data);
    }
}