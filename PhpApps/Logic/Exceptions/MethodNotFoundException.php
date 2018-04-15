<?php
namespace Logic\Exceptions;

use Nette\Http\IResponse;

class MethodNotFoundException extends JsonRpcException
{
    /**
     * MethodNotFoundException constructor.
     * @param string|null $data
     */
    public function __construct(string $data = null)
    {
        parent::__construct(Iresponse::S404_NOT_FOUND, 'Method not found', -32601, $data);
    }
}