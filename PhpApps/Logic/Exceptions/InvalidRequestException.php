<?php
namespace Logic\Exceptions;

class InvalidRequestException extends JsonRpcException
{
    /**
     * InvalidRequestException constructor.
     * @param string|null $data
     */
    public function __construct(string $data = null)
    {
        parent::__construct(-32600, 'Invalid Request', $data);
    }
}