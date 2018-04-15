<?php

namespace Presenters;

use Logic\ApiHandler;
use Logic\ApiRouter;
use Nette;
use Nette\Application\BadRequestException;
use Nette\Application\Request;

class ApiPresenter extends Nette\Application\UI\Presenter
{
    /** @var ApiRouter */
    private $apiRouter;
    /** @var ApiHandler */
    private $apiHandler;

    /**
     * ApiPresenter constructor.
     * @param ApiRouter $apiRouter
     * @param ApiHandler $apiHandler
     */
    public function __construct(ApiRouter $apiRouter, ApiHandler $apiHandler)
    {
        parent::__construct();
        $this->apiRouter = $apiRouter;
        $this->apiHandler = $apiHandler;
    }

    /**
     *
     */
    public function renderDefault(): void
    {
        $request = $this->getRequest();
        try {
            if ($request === null) {
                throw new \RuntimeException('Request missing');
            }
            $this->checkJsonRpc($request);
            $this->apiHandler->handle(
                $this->getEndpointClass($request), $this->getMethod($request), $this->parseParams($request));
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    private function handleException(\Exception $e)
    {

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

        return $endpoint;
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