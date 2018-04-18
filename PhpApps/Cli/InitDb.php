<?php

class InitDb
{
    private const PLAYER = 'player';
    private const GAME = 'game';
    private const SCOREMAP = 'scoreMap';
    private const SCORE = 'score';

    /** @var resource */
    private $curl;
    /** @var int */
    private $requestId = 0;

    private $players = [
        ['name' => 'Tonda', 'city' => 'Tondov'],
        ['name' => 'Jan', 'city' => 'Janov'],
        ['name' => 'Martin', 'city' => 'Martinov'],
        ['name' => 'Tonda']
    ];

    private $games = [
        ['name' => 'Game 1'],
        ['name' => 'Game 2'],
        ['name' => 'Game 3']
    ];

    private $scoreMaps = [
        ['gameId' => 3, 'playerId' => 3],
        ['gameId' => 3, 'playerId' => 1],
        ['gameId' => 3, 'playerId' => 2],
        ['gameId' => 1, 'playerId' => 1],
        ['gameId' => 1, 'playerId' => 2],
        ['gameId' => 1, 'playerId' => 3],
        ['gameId' => 2, 'playerId' => 1],
        ['gameId' => 2, 'playerId' => 3],
        ['gameId' => 3, 'playerId' => 4],
    ];

    private $scores = [
        // Game 3
        ['gameId' => 3, 'playerId' => 3, 'score' => 1050],
        ['gameId' => 3, 'playerId' => 3, 'score' => 900],
        ['gameId' => 3, 'playerId' => 3, 'score' => 1200],
        ['gameId' => 3, 'playerId' => 3, 'score' => 700],
        ['gameId' => 3, 'playerId' => 3, 'score' => 800],
        ['gameId' => 3, 'playerId' => 1, 'score' => 800],
        ['gameId' => 3, 'playerId' => 1, 'score' => 1200],
        ['gameId' => 3, 'playerId' => 1, 'score' => 1200],
        ['gameId' => 3, 'playerId' => 1, 'score' => 1350],
        ['gameId' => 3, 'playerId' => 2, 'score' => 900],
        ['gameId' => 3, 'playerId' => 2, 'score' => 750],
        ['gameId' => 3, 'playerId' => 2, 'score' => 785],
        ['gameId' => 3, 'playerId' => 2, 'score' => 1345],
        ['gameId' => 3, 'playerId' => 4, 'score' => 1375],
        ['gameId' => 3, 'playerId' => 4, 'score' => 1000],
        ['gameId' => 3, 'playerId' => 4, 'score' => 500],
        // Game 1
        ['gameId' => 1, 'playerId' => 1, 'score' => 1],
        ['gameId' => 1, 'playerId' => 1, 'score' => 2],
        ['gameId' => 1, 'playerId' => 1, 'score' => 3],
        ['gameId' => 1, 'playerId' => 1, 'score' => 4],
        ['gameId' => 1, 'playerId' => 1, 'score' => 3],
        ['gameId' => 1, 'playerId' => 2, 'score' => 2],
        ['gameId' => 1, 'playerId' => 2, 'score' => 2],
        ['gameId' => 1, 'playerId' => 2, 'score' => 3],
        ['gameId' => 1, 'playerId' => 2, 'score' => 1],
        ['gameId' => 1, 'playerId' => 3, 'score' => 4],
    ];

    /**
     * InitDb constructor.
     */
    public function __construct()
    {
        $this->curl = curl_init();
        curl_setopt($this->curl, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($this->curl, CURLOPT_RETURNTRANSFER, true);
    }

    public function __destruct()
    {
        curl_close($this->curl);
    }

    /**
     * @return int
     */
    public function run(): int
    {
        // games
        $this->setEndpoint(self::GAME);
        foreach ($this->games as $game) {
            if (!$this->exec($game)) {
                return 1;
            }
        }

        // players
        $this->setEndpoint(self::PLAYER);
        foreach ($this->players as $player) {
            if (!$this->exec($player)) {
                return 1;
            }
        }

        // scoreMap
        $this->setEndpoint(self::SCOREMAP);
        foreach ($this->scoreMaps as $scoreMap) {
            if (!$this->exec($scoreMap)) {
                return 1;
            }
        }

        // score
        $this->setEndpoint(self::SCORE);
        foreach ($this->scores as $score) {
            if (!$this->exec($score)) {
                return 1;
            }
        }

        return 0;
    }

    /**
     * @param string $endpoint
     */
    public function setEndpoint(string $endpoint): void
    {
        curl_setopt($this->curl, CURLOPT_URL, '127.0.0.1:8080/api/' . $endpoint);
    }

    /**
     * @param array $params
     * @return string
     */
    private function createBody(array $params): string
    {
        return json_encode([
            'jsonrpc' => '2.0',
            'method' => 'insert',
            'params' => $params,
            'id' => $this->requestId++
        ]);
    }

    /**
     * @param array $params
     * @return bool
     */
    private function exec(array $params): bool
    {
        $body = $this->createBody($params);

        curl_setopt($this->curl, CURLOPT_POSTFIELDS, $body);
        curl_setopt($this->curl, CURLOPT_HTTPHEADER, array(
                'Content-Type: application/json',
                'Content-Length: ' . strlen($body))
        );

        $result = curl_exec($this->curl);

        return $this->checkResult($result, $body);
    }

    /**
     * @param string|bool $result
     * @param string $body
     * @return bool
     */
    private function checkResult($result, string $body): bool
    {
        if ($result === false) {
            echo "CURL call failed.\nBody: $body\n";
            echo 'Flush your DB! Redis cmd - FLUSHALL';

            return false;
        }

        $return = json_decode($result);

        if (isset($return->error)) {
            echo "ERROR: {$return->error->message}\nBody: $body\n";
            echo 'Flush your DB! Redis cmd - FLUSHALL';

            return false;
        }

        return true;
    }
}

(new InitDb())->run();
