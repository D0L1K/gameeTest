<?php
namespace Logic\Exceptions;

class InternalErrorException extends JsonRpcException
{
    /**
     * InternalErrorException constructor.
     * @param string|null $data
     */
    public function __construct(string $data = null)
    {
        parent::__construct(-32603, 'Internal error', $data);
    }
}