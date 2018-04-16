<?php
namespace Logic;

class ConnectParams
{
    /** @var string */
    private $host;
    /** @var int */
    private $port;
    /** @var int */
    private $database;
    /** @var int|null */
    private $timeout;
    /** @var string|null */
    private $auth;
    /** @var bool */
    private $persistent;

    /**
     * ConnectParams constructor.
     * @param string $host
     * @param int $port
     * @param int $database
     * @param int|null $timeout
     * @param string|null $auth
     * @param bool $persistent
     */
    public function __construct(
        string $host = '127.0.0.1',
        int $port = 6379,
        int $database = 0,
        int $timeout = null,
        string $auth = null,
        bool $persistent = false)
    {
        $this->host = $host;
        $this->port = $port;
        $this->database = $database;
        $this->timeout = $timeout;
        $this->auth = $auth;
        $this->persistent = $persistent;
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @return int
     */
    public function getDatabase(): int
    {
        return $this->database;
    }

    /**
     * @return int|null
     */
    public function getTimeout(): ?int
    {
        return $this->timeout;
    }

    /**
     * @return null|string
     */
    public function getAuth(): ?string
    {
        return $this->auth;
    }

    /**
     * @return bool
     */
    public function isPersistent(): bool
    {
        return $this->persistent;
    }
}