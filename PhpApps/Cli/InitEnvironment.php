<?php

class InitEnvironment
{
    /** @var string */
    private $apachePath;
    /** @var string */
    private $redisPath;
    /** @var string */
    private $phpPath;
    /** @var bool */
    private $error = false;

    /**
     * InitEnvironment constructor.
     */
    public function __construct()
    {
        $this->apachePath = realpath(__DIR__ . '\\..\\..\\Apache24\\');
        $this->redisPath = realpath(__DIR__ . '\\..\\..\\Redis\\');
        $this->phpPath = realpath(__DIR__ . '\\..\\..\\PHP\\');
    }

    /**
     * @return int
     * @throws \RuntimeException
     */
    public function run(): int
    {
        try {
            $httpdConf = $this->loadHttpsConfTemplate();
            $httpdConf = $this->fillHttpdConfVars($httpdConf);
            $this->createHttpdConf($httpdConf);
            $phpIni = $this->loadPhpIniTemplate();
            $phpIni = $this->fillPhpIniVars($phpIni);
            $this->createPhpIni($phpIni);
            $this->createApacheService();
            $this->startApache();
            $this->createRedisService();
            $this->startRedis();
        } catch (\Exception $e) {
            $this->echoResult('Error: ' . $e->getMessage());
            $this->error = true;
        }

        if ($this->error) {
            echo "\nThere was an error starting server. Check output, do uninstall and try again.";

            return 1;
        }

        echo "\nServer started, waiting for requests on 127.0.0.1:8080\nHave Fun!";

        return 0;
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
                $this->apachePath,
                $this->phpPath,
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
        $httpdConfPath = $this->apachePath . '\\conf\\httpd.conf';
        $fp = fopen($httpdConfPath, 'wb');
        $result = fwrite($fp, $httpdConf);
        if ($result === false) {
            throw new \RuntimeException('Unable to create httpd.conf');
        }
        $this->echoResult('Created.');
    }

    /**
     * @return string
     * @throws \RuntimeException
     */
    private function loadPhpIniTemplate(): string
    {
        $this->echoStep('Loading php.ini template');
        $phpIniTemplate = realpath(__DIR__ . '\\..\\..\\res\\php-template.ini');
        if (!file_exists($phpIniTemplate)) {
            throw new \RuntimeException('Php.ini template not found');
        }

        $fp = fopen($phpIniTemplate, 'rb');
        $content = fread($fp, filesize($phpIniTemplate));
        if ($content === false) {
            throw new \RuntimeException('Unable to read php.ini template');
        }
        $this->echoResult('Loaded.');

        return $content;
    }

    /**
     * @param string $phpIni
     * @return string
     */
    private function fillPhpIniVars(string $phpIni): string
    {
        $this->echoStep('Configuring php.ini');
        $content = str_replace('%PHPROOT%', $this->phpPath, $phpIni);
        $this->echoResult('Configured.');

        return $content;
    }

    /**
     * @param string $phpIni
     */
    private function createPhpIni(string $phpIni): void
    {
        $this->echoStep('Creating php.ini');
        $phpIniPath = $this->phpPath . '\\php.ini';
        $fp = fopen($phpIniPath, 'wb');
        $result = fwrite($fp, $phpIni);
        if ($result === false) {
            throw new \RuntimeException('Unable to create php.ini');
        }
        $this->echoResult('Created.');
    }

    /**
     * @return bool
     */
    private function createApacheService(): bool
    {
        $this->echoStep('Creating Apache service');

        $httpdExe = realpath($this->apachePath . '\\bin\\httpd.exe');
        exec('"' . $httpdExe . '" -k install -n "Apache24"', $output, $return);
        if ($return !== 0) {
            $output = "\n" . implode("\n", $output);
            $this->echoResult("Creating Apache service failed!\n\nExit code: $return\nOutput: $output");
            $this->error = true;

            return false;
        }

        $this->echoResult('Created.');

        return true;
    }

    /**
     * @return bool
     */
    private function startApache(): bool
    {
        $this->echoStep('Starting Apache');
        exec('sc start Apache24', $output, $return);
        if ($return !== 0) {
            $output = "\n" . implode("\n", $output);
            $this->echoResult("Starting Apache service failed!\n\nExit code: $return\nOutput: $output");
            $this->error = true;

            return false;
        }

        $this->echoResult('Started.');

        return true;
    }

    /**
     * @return bool
     */
    private function createRedisService(): bool
    {
        $this->echoStep('Creating Redis service');
        $redisExe = realpath($this->redisPath . '\\redis-server.exe');
        $redisConf = realpath($this->redisPath . '\\redis.windows-service.conf');

        exec('"' . $redisExe . '" --service-install "' . $redisConf . '" --service-name redis6379 --port 6379',
            $output, $return );
        if ($return !== 1) {
            $output = "\n" . implode("\n", $output);
            $this->echoResult("Creating Redis service failed!\n\nExit code: $return\nOutput: $output");
            $this->error = true;

            return false;
        }

        $this->echoResult('Created.');

        return true;
    }

    /**
     * @return bool
     */
    private function startRedis(): bool
    {
        $this->echoStep('Starting Redis');
        exec('sc start redis6379',$output, $return );
        if ($return !== 0) {
            $output = "\n" . implode("\n", $output);
            $this->echoResult("Starting Redis service failed!\n\nExit code: $return\nOutput: $output");
            $this->error = true;

            return false;
        }

        $this->echoResult('Started.');

        return true;
    }
}

(new InitEnvironment())->run();