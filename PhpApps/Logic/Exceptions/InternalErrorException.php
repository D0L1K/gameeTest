<?php
namespace Logic\Exceptions;

use Nette\Http\IResponse;

class InternalErrorException extends JsonRpcException
{
    /**
     * InternalErrorException constructor.
     * @param string|null $data
     */
    public function __construct(string $data = null)
    {
        parent::__construct(Iresponse::S500_INTERNAL_SERVER_ERROR, 'Internal error', -32603, $data);
    }
}