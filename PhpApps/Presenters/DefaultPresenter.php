<?php
namespace Presenters;

use Nette;

class DefaultPresenter extends Nette\Application\UI\Presenter
{
    public function renderDefault()
    {
        $request = $this->getRequest();
    }
}