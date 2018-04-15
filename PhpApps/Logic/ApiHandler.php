<?php
namespace Logic;

use Logic\Exceptions\InvalidRequestException;
use Logic\Exceptions\ParseErrorException;
use Nette\DI\Container;
use Nette\Application\BadRequestException;
use Nette\Http\Request;

class ApiHandler
{
    /** @var Container  */
    private $container;
    /** @var ApiRouter */
    private $apiRouter;


    /**
     * ApiHandler constructor.
     * @param Container $container
     * @param ApiRouter $apiRouter
     */
    public function __construct(Container $container, ApiRouter $apiRouter)
    {
        $this->container = $container;
        $this->apiRouter = $apiRouter;
    }

    /**
     * @param Request $httpRequest
     * @param \Nette\Application\Request $request
     * @return mixed
     * @throws BadRequestException
     */
    public function handle(Request $httpRequest, \Nette\Application\Request $request)
    {
        try {
            if ($httpRequest->getMethod() !== Request::POST) {
                throw new InvalidRequestException('Must be POST call');
            }
            $rpcRequest = $this->parseJsonRpcRequest($httpRequest);
            $classObj = $this->container->createInstance($this->getEndpointClass($request));
            $method = $rpcRequest->getMethod();
            $params = $rpcRequest->getParams();
            $result = $classObj->$method($params);

        } catch (\Exception $e) {
            $this->handleException($e);
        }

        return $result;
    }

    /**
     * @param \Nette\Application\Request $request
     * @return string
     * @throws BadRequestException
     */
    private function getEndpointClass(\Nette\Application\Request $request): string
    {
        $endpoint = $request->getParameter('endpoint');
        if (!\is_string($endpoint) || $endpoint === '') {
            throw new BadRequestException('API URL in wrong format - missing endpoint name');
        }

        $endpointClass = $this->apiRouter->getEndpointClass($endpoint);
        if ($endpointClass === null) {
            throw new BadRequestException("Handler class for endpoint \{$endpoint\} not found");
        }

        return $endpointClass;
    }

    /**
     * @param Request $httpRequest
     * @return JsonRpcRequest
     * @throws InvalidRequestException
     * @throws ParseErrorException
     */
    private function parseJsonRpcRequest(Request $httpRequest): JsonRpcRequest
    {
        $body = $httpRequest->getRawBody();
        $parsedBody = json_decode($body);
        if ($parsedBody === null) {
            throw new ParseErrorException();
        }
        $jsonRpcParam = $parsedBody->jsonrpc ?? null;
        if ($jsonRpcParam === null) {
            throw new InvalidRequestException('Missing \'jsonrpc\' param');
        }
        if ($jsonRpcParam !== '2.0') {
            throw new InvalidRequestException('Param\'jsonrpc\' has wrong value. Expected: 2.0, got: '. $jsonRpcParam);
        }
        $methodParam = $parsedBody->method ?? null;
        if (!\is_string($methodParam) || $methodParam === '') {
            throw new InvalidRequestException('Missing or empty \'method\' param');
        }
        $paramsParam = $parsedBody->params ?? null;
        if ($paramsParam === null) {
            throw new InvalidRequestException('Missing \'params\' param');
        }
        $idParam = $parsedBody->id ?? null;
        if ($idParam === null) {
            throw new InvalidRequestException('Missing \'id\' param');
        }

        return new JsonRpcRequest($jsonRpcParam, $methodParam, $idParam, $paramsParam);
    }
}