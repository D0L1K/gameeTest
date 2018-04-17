<?php
namespace Logic;

use Api\Game;
use Api\Player;
use Api\Score;
use Api\PlayerGame;

class ApiRouter
{
    /** @var array */
    private $routes = [];
    /** @var bool */
    private $inited = false;

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
            'score' => Score::class,
            'player' => Player::class,
            'game' => Game::class,
            'playergame' => PlayerGame::class
        ];

        $this->inited = true;
    }

    /**
     * @param string $endpoint
     * @return null|string
     */
    public function getEndpointClass(string $endpoint): ?string
    {
        return $this->routes[strtolower($endpoint)] ?? null;
    }
}