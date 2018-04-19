<?php

class InitEnvironment
{
    /** @var string */
    private $apachePath;
    /** @var string */
    private $redisPath;

    public function __construct()
    {
        $this->apachePath = realpath(__DIR__ . '\\..\\..\\Apache24\\');
        $this->redisPath = realpath(__DIR__ . '\\..\\..\\Redis\\');
    }

    /**
     * @return int
     * @throws \RuntimeException
     */
    public function run(): int
    {
        try {
            $httpdConf = $this->loadHttpsConfTemplate();;
            $httpdConf = $this->fillHttpdConfVars($httpdConf);
            $this->createHttpdConf($httpdConf);
            $this->createApacheService();
            $this->startApache();
            $this->createRedisService();
            $this->startRedis();

            echo "\nServer started, waiting for requests on 127.0.0.1:8080\nHave Fun!";

            return 0;
        } catch (\Exception $e) {
            $this->echoResult('Error: ' . $e->getMessage());

            return 1;
        }
    }

    /**
     * @param string $msg
     */
    private function echoStep(string $msg): void
    {
        $len = 40 - (strlen($msg) + 6);
        for ($i = 0; $i < 40; $i++) {
            echo '|';
        }
        echo "\n|| $msg";
        for ($i = 0; $i < $len; $i++) {
            echo ' ';
        }
        echo " ||\n";
        for ($i = 0; $i < 40; $i++) {
            echo '|';
        }
        echo "\n";
    }

    /**
     * @param string $msg
     */
    private function echoResult(string $msg): void
    {
        echo "\n" . $msg . "\n\n\n";
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    private function loadHttpsConfTemplate(): string
    {
        $this->echoStep('Loading httpd.conf template');
        $httpdTemplate = realpath(__DIR__ . '\\..\\..\\res\\httpd-template.conf');
        if (!file_exists($httpdTemplate)) {
            throw new \RuntimeException('Httpd template not found');
        }

        $fp = fopen($httpdTemplate, 'rb');
        $content = fread($fp, filesize($httpdTemplate));
        if ($content === false) {
            throw new \RuntimeException('Unable to read httpd template');
        }
        $this->echoResult('Loaded.');

        return $content;
    }

    /**
     * @param string $httpdConf
     * @return string
     */
    private function fillHttpdConfVars(string $httpdConf): string
    {
        $this->echoStep('Configuring httpd.conf');
        $content = str_replace(
            [
                '%SRVROOT%',
                '%PHPROOT%',
                '%PHPAPPSROOT%'],
            [
                realpath($this->apachePath),
                realpath(__DIR__ . '\\..\\..\\PHP\\'),
                realpath(__DIR__ . '\\..\\')
            ], $httpdConf);
        $this->echoResult('Configured.');

        return $content;
    }

    /**
     * @param string $httpdConf
     */
    private function createHttpdConf(string $httpdConf): void
    {
        $this->echoStep('Creating httpd.conf');
        $httpdTemplate = $this->apachePath . '\\conf\\httpd.conf';
        $fp = fopen($httpdTemplate, 'wb');
        $result = fwrite($fp, $httpdConf);
        if ($result === false) {
            throw new \RuntimeException('Unable to create httpd.conf');
        }
        $this->echoResult('Created.');
    }

    private function createApacheService(): void
    {
        $this->echoStep('Creating Apache service');

        $httpdExe = realpath($this->apachePath . '\\bin\\httpd.exe');
        exec('"' . $httpdExe . '" -k install -n "Apache24"', $output, $return);
        if ($return !== 0) {
            $output = "\n" . implode("\n", $output);
            throw new \RuntimeException("Creating Apache service failed!\n\nExit code: $return\nOutput: $output");
        }
        $this->echoResult('Created.');
    }

    private function startApache(): void
    {
        $this->echoStep('Starting Apache');
        exec('sc start Apache24', $output, $return);
        if ($return !== 0) {
            $output = "\n" . implode("\n", $output);
            throw new \RuntimeException("Starting Apache service failed!\n\nExit code: $return\nOutput: $output");
        }
        $this->echoResult('Started.');
    }

    private function createRedisService(): void
    {
        $this->echoStep('Creating Redis service');
        $redisExe = realpath($this->redisPath . '\\redis-server.exe');
        $redisConf = realpath($this->redisPath . '\\redis.windows-service.conf');

        exec('"' . $redisExe . '" --service-install "' . $redisConf . '" --service-name redis6379 --port 6379',
            $output, $return );
        if ($return !== 0) {
            $output = "\n" . implode("\n", $output);
            throw new \RuntimeException("Creating Redis service failed!\n\nExit code: $return\nOutput: $output");
        }
        $this->echoResult('Created.');
    }

    private function startRedis(): void
    {
        $this->echoStep('Starting Redis');
        exec('sc start redis6379',$output, $return );
        if ($return !== 0) {
            $output = "\n" . implode("\n", $output);
            throw new \RuntimeException("Starting Redis service failed!\n\nExit code: $return\nOutput: $output");
        }
        $this->echoResult('Started.');
    }
}

(new InitEnvironment())->run();