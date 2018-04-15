<?php
namespace Logic\Exceptions;

use Nette\Http\IResponse;

class ParseErrorException extends JsonRpcException
{
    /**
     * ParseErrorException constructor.
     * @param string|null $data
     */
    public function __construct(string $data = null)
    {
        parent::__construct(Iresponse::S400_BAD_REQUEST, 'Parse error', -32700, $data);
    }
}