<?php

namespace Presenters;

use Logic\ApiHandler;
use Logic\ApiRouter;
use Nette;

class ApiPresenter extends Nette\Application\UI\Presenter
{
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
            $this->apiHandler->handle($request);
        } catch (\Exception $e) {
            $this->handleException($e);
        }
    }

    private function handleException(\Exception $e)
    {

    }
}