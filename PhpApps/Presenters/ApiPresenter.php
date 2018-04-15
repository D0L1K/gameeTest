<?php
namespace Presenters;

use Nette;

class ApiPresenter extends Nette\Application\UI\Presenter
{
    public function renderDefault()
    {
        $request = $this->getRequest();
    }
}