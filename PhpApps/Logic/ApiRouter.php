<?php
namespace Logic;

use Api\Score;

class ApiRouter
{
    /** @var array */
    private $routes;
    /** @var bool */
    private $inited;

    /**
     * ApiRouter constructor.
     */
    public function __construct()
    {
        $this->init();
    }

    /**
     * @return void
     */
    private function init(): void
    {
        if ($this->inited) {
            return;
        }

        $this->routes = [
            'score' => Score::class
        ];

        $this->inited = true;
    }
}