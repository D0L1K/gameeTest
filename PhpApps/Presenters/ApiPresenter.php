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
        $result = $this->apiHandler->handle($this->getHttpRequest(), $this->getRequest());
    }
}