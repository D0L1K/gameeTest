<?php
namespace Logic;

use Logic\Exceptions\InvalidParamsException;
use Logic\Exceptions\InvalidRequestException;
use Logic\Exceptions\JsonRpcException;
use Logic\Exceptions\ParseErrorException;
use Nette\DI\Container;
use Nette\Application\BadRequestException;
use Nette\Http\IRequest;
use Nette\Http\IResponse;
use Nette\Http\Request;
use ReflectionMethod;
use ReflectionParameter;

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
     * @param Session $session
     */
    public function __construct(Container $container, ApiRouter $apiRouter, Session $session)
    {
        $this->container = $container;
        $this->apiRouter = $apiRouter;
        // @codesmell: Initializing of Session for every API call
    }

    /**
     * @param IRequest $httpRequest
     * @param \Nette\Application\Request $request
     * @return JsonRpcResponse
     */
    public function handle(IRequest $httpRequest, \Nette\Application\Request $request): JsonRpcResponse
    {
        $response = new JsonRpcResponse();
        try {
            if ($httpRequest->getMethod() !== Request::POST) {
                throw new InvalidRequestException('Must be POST call');
            }
            $rpcRequest = $this->parseJsonRpcRequest($httpRequest);
            $response->setId($rpcRequest->getId());
            $className = $this->getEndpointClass($request);
            $classObj = $this->container->createInstance($className);
            $method = $rpcRequest->getMethod();
            $params = $rpcRequest->getParams();
            $orderedParams = $this->getMethodParams($className, $method, $params);
            $methodResult = \call_user_func_array([$classObj, $method], $orderedParams);
            $response->setResult($methodResult);
        } catch (\Exception $e) {
            if (!($e instanceof JsonRpcException)) {
                $e = new JsonRpcException(IResponse::S500_INTERNAL_SERVER_ERROR, $e->getMessage());
            }
            $response->setError($e);
        }

        return $response;
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
            throw new BadRequestException("Handler class for endpoint '$endpoint' not found");
        }

        return $endpointClass;
    }

    /**
     * @param IRequest $httpRequest
     * @return JsonRpcRequest
     * @throws InvalidRequestException
     * @throws ParseErrorException
     */
    private function parseJsonRpcRequest(IRequest $httpRequest): JsonRpcRequest
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

    /**
     * @param string $className
     * @param string $method
     * @param \stdClass|null $params
     * @return array
     * @throws \ReflectionException
     * @throws InvalidParamsException
     */
    private function getMethodParams(string $className, string $method, \stdClass $params = null): array
    {
        $r = new ReflectionMethod($className, $method);
        /** @var ReflectionParameter[] $methodParams */
        $methodParams = $r->getParameters();
        if ($params === null && \count($methodParams) > 0) {
            throw new InvalidParamsException('No params provided, however method needs params');
        }
        $orderedParams = [];
        foreach ($methodParams as $methodParam) {
            $name = $methodParam->getName();
            if (!isset($params->$name) && !$methodParam->isOptional()) {
                throw new InvalidParamsException("Missing required parameter '$name'");
            }
            if (!isset($params->$name) && $methodParam->isOptional()) {
                $orderedParams[] = null;
            } elseif (isset($params->$name)) {
                $orderedParams[] = $params->$name;
            }
        }

        return $orderedParams;
    }
}