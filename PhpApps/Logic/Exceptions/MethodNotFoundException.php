<?php
namespace Logic\Exceptions;

class MethodNotFoundException extends JsonRpcException
{
    /**
     * MethodNotFoundException constructor.
     * @param string|null $data
     */
    public function __construct(string $data = null)
    {
        parent::__construct(-32601, 'Method not found', $data);
    }
}