<?php
namespace Logic;

use Nette\DI\Container;
use Nette\Application\BadRequestException;
use Nette\Application\Request;

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
     * @param Request $request
     * @return mixed
     * @throws \Nette\InvalidArgumentException
     * @throws BadRequestException
     */
    public function handle(Request $request)
    {
        $this->checkJsonRpc($request);
        $classObj = $this->container->createInstance($this->getEndpointClass($request));
        $method = $this->getMethod($request);
        $params = $this->parseParams($request);

        return $classObj->$method($params);
    }

    /**
     * @param Request $request
     * @throws BadRequestException
     */
    private function checkJsonRpc(Request $request): void
    {
        $jsonRpcParam = $request->getParameter('jsonrpc');
        if ($jsonRpcParam === null) {
            $this->raiseJsonRpcExcpetion('Missing \'jsonrpc\' param');
        }
        if ($jsonRpcParam !== '2.0') {
            $this->raiseJsonRpcExcpetion('Param\'jsonrpc\' has wrong value. Expected: 2.0, got: '. $jsonRpcParam);
        }
    }

    /**
     * @param string $error
     * @throws BadRequestException
     */
    private function raiseJsonRpcExcpetion(string $error): void
    {
        throw new BadRequestException('Request is not valid JSON RPC call - ' . $error);
    }

    /**
     * @param Request $request
     * @return string
     * @throws BadRequestException
     */
    private function getEndpointClass(Request $request): string
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
     * @param Request $request
     * @return string
     * @throws BadRequestException
     */
    private function getMethod(Request $request): string
    {
        $method = $request->getParameter('method');
        if (!\is_string($method) || $method === '') {
            $this->raiseJsonRpcExcpetion('Missing \'method\' param');
        }

        return $method;
    }

    /**
     * @param Request $request
     * @return array|null
     */
    private function parseParams(Request $request): ?array
    {
        $jsonParams = $request->getParameter('params');

        return json_decode($jsonParams, true);
    }
}