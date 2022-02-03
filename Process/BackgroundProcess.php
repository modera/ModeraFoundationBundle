<?php

namespace Modera\FoundationBundle\Process;

use Symfony\Component\Process\Process;

/**
 * @author    Sergei Vizel <sergei.vizel@modera.org>
 * @copyright 2018 Modera Foundation
 */
class BackgroundProcess extends Process
{
    /**
     * {@inheritdoc}
     */
    public function __destruct()
    {
        // overwritten to prevent kill process
    }

    /**
     * {@inheritdoc}
     */
    public function start(callable $callback = null, array $env = array())
    {
        $commandline = $this->getCommandLine();
        static::prepare($this);
        parent::start($callback, $env);
        static::overrideCommandLine($this, $commandline);
    }

    /**
     * @param Process $process
     */
    public static function prepare(Process $process)
    {
        $commandline = $process->getCommandLine();
        if ('\\' === \DIRECTORY_SEPARATOR) {
            $commandline = 'START /b "" ' . $commandline;
        } else {
            $commandline = 'nohup ' . $commandline . ' >/dev/null 2>&1 & echo $!';
        }
        static::overrideCommandLine($process, $commandline);
    }

    protected static function overrideCommandLine(Process $process, string $commandline)
    {
        $property = new \ReflectionProperty(Process::class, 'commandline');
        $property->setAccessible(true);
        $property->setValue($process, $commandline);
    }
}
