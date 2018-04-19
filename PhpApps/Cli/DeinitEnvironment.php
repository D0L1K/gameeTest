<?php

class DeinitEnvironment
{
    /**
     * @return int
     */
    public function run(): int
    {
        try {
            $this->stopApache();
            $this->stopRedis();
            // TODO: Services should be checked if they actually stopped
            sleep(1);
            $this->removeApacheService();
            $this->removeRedisService();

            echo "\nEverything stopped, deleted, destroyed, erased, eliminated.\nHope you had Fun you were supposed to have!";

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
     * @throws \RuntimeException
     */
    private function stopApache(): void
    {
        $this->echoStep('Stopping Apache');
        exec('sc stop Apache24', $output, $return);
        if ($return !== 0) {
            $output = "\n" . implode("\n", $output);
            throw new \RuntimeException("Stopping Apache service failed!\n\nExit code: $return\nOutput: $output");
        }
        $this->echoResult('Stopped.');
    }

    /**
     * @throws \RuntimeException
     */
    private function stopRedis(): void
    {
        $this->echoStep('Stopping Redis');
        exec('sc stop redis6379', $output, $return);
        if ($return !== 0) {
            $output = "\n" . implode("\n", $output);
            throw new \RuntimeException("Stopping Redis service failed!\n\nExit code: $return\nOutput: $output");
        }
        $this->echoResult('Stopped.');
    }

    /**
     * @throws \RuntimeException
     */
    private function removeApacheService(): void
    {
        $this->echoStep('Deleting Apache service');
        exec('sc delete Apache24', $output, $return);
        if ($return !== 0) {
            $output = "\n" . implode("\n", $output);
            throw new \RuntimeException("Deleting Apache service failed!\n\nExit code: $return\nOutput: $output");
        }
        $this->echoResult('Deleted.');
    }

    /**
     * @throws \RuntimeException
     */
    private function removeRedisService(): void
    {
        $this->echoStep('Deleting Redis service');
        exec('sc delete redis6379', $output, $return);
        if ($return !== 0) {
            $output = "\n" . implode("\n", $output);
            throw new \RuntimeException("Deleting Redis service failed!\n\nExit code: $return\nOutput: $output");
        }
        $this->echoResult('Deleted.');
    }
}

(new DeinitEnvironment())->run();