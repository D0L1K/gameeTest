<?php
namespace Logic\Exceptions;

class ParseErrorException extends JsonRpcException
{
    /**
     * ParseErrorException constructor.
     * @param string|null $data
     */
    public function __construct(string $data = null)
    {
        parent::__construct(-32700, 'Parse error', $data);
    }
}