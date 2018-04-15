<?php
namespace Presenters;

use Logic\ApiRouter;
use Nette;

class ApiPresenter extends Nette\Application\UI\Presenter
{
    /** @var ApiRouter  */
    private $apiRouter;

    /**
     * ApiPresenter constructor.
     * @param ApiRouter $apiRouter
     */
    public function __construct(ApiRouter $apiRouter)
    {
        $this->apiRouter = $apiRouter;
    }

    public function renderDefault()
    {
        $request = $this->getRequest();
    }
}