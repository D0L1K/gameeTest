<?php

class DeinitEnvironment
{
    /** @var string */
    private $apachePath;
    /** @var string */
    private $redisPath;
    /** @var bool */
    private $error = false;

    /**
     * DeinitEnvironment constructor.
     */
    public function __construct()
    {
        $this->apachePath = realpath(__DIR__ . '\\..\\..\\Apache24\\');
        $this->redisPath = realpath(__DIR__ . '\\..\\..\\Redis\\');
    }

    /**
     * @return int
     */
    public function run(): int
    {
        try {
            $this->stopApache();
            // TODO: Services should be checked if they actually stopped
            sleep(1);
            $this->removeApacheService();
            $this->stopRedis();
            sleep(1);
            $this->removeRedisService();
            sleep(1);
            $this->deleteCreatedFiles();
        } catch (\Exception $e) {
            $this->echoResult('Error: ' . $e->getMessage());
            $this->error = true;
        }

        if ($this->error) {
            echo "\nThere was an error stoping server. Check output and try again.";

            return 1;
        }

        echo "\nEverything stopped, deleted, destroyed, erased, eliminated.\nHope you had Fun you were supposed to have!";

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

    private function stopApache(): void
    {
        $this->echoStep('Stopping Apache');
        exec('sc stop Apache24', $output, $return);
        if ($return !== 0) {
            $output = "\n" . implode("\n", $output);
            $this->echoResult("Stopping Apache service failed!\n\nExit code: $return\nOutput: $output");
            $this->error = true;
        } else {
            $this->echoResult('Stopped.');
        }
    }

    private function stopRedis(): void
    {
        $this->echoStep('Stopping Redis');
        exec('sc stop redis6379', $output, $return);
        if ($return !== 0) {
            $output = "\n" . implode("\n", $output);
            $this->echoResult("Stopping Redis service failed!\n\nExit code: $return\nOutput: $output");
            $this->error = true;
        } else {
            $this->echoResult('Stopped.');
        }
    }

    private function removeApacheService(): void
    {
        $this->echoStep('Deleting Apache service');
        exec('sc delete Apache24', $output, $return);
        if ($return !== 0) {
            $output = "\n" . implode("\n", $output);
            $this->echoResult("Deleting Apache service failed!\n\nExit code: $return\nOutput: $output");
            $this->error = true;
        } else {
            $this->echoResult('Deleted.');
        }
    }

    private function removeRedisService(): void
    {
        $this->echoStep('Deleting Redis service');
        exec('sc delete redis6379', $output, $return);
        if ($return !== 0) {
            $output = "\n" . implode("\n", $output);
            $this->echoResult("Deleting Redis service failed!\n\nExit code: $return\nOutput: $output");
            $this->error = true;
        } else {
            $this->echoResult('Deleted.');
        }
    }

    private function deleteCreatedFiles(): void
    {
        $this->echoStep('Deleting created files');
        $this->flushFolder($this->apachePath . '\\logs\\');
        @unlink($this->redisPath . '\\dump.rdb');
        @unlink($this->redisPath . '\\server_log.txt');
        $this->echoResult('Deleted.');
    }

    /**
     * @param string $path
     */
    private function flushFolder(string $path): void
    {
        $files = glob($path . '*');
        foreach ($files as $file) {
            if (is_file($file)) {
                @unlink($file);
            }
        }
    }
}

(new DeinitEnvironment())->run();