<?php
/*
 * This file is part of the SoureCode package.
 *
 * (c) Jason Schilling <jason@sourecode.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SoureCode\Bundle\Action\Command;

use InvalidArgumentException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Filesystem\Filesystem;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
abstract class AbstractActionDaemonCommand extends Command
{
    protected array $daemons;

    protected Filesystem $filesystem;

    protected string $logsDirectory;

    protected string $projectDirectory;

    protected function getLogsErroutFilepath(string $daemonName): string
    {
        return $this->logsDirectory.'/daemon.'.$daemonName.'.errout.log';
    }

    protected function getLogsStdoutFilepath(string $daemonName): string
    {
        return $this->logsDirectory.'/daemon.'.$daemonName.'.stdout.log';
    }

    protected function isDaemonRunning(string $daemonName): bool
    {
        $pidFilepath = $this->getPidFilepath($daemonName);

        return file_exists($pidFilepath);
    }

    protected function getPidFilepath(string $daemonName): string
    {
        $daemonDirectory = $this->ensureDaemonDirectory();

        return $daemonDirectory.'/'.$daemonName.'.pid';
    }

    protected function ensureDaemonDirectory(): string
    {
        $directory = $this->projectDirectory.'/var/daemons';

        $this->filesystem->mkdir($directory);

        return $directory;
    }

    protected function validateDaemonName(string $daemonName): void
    {
        if (!\array_key_exists($daemonName, $this->daemons)) {
            throw new InvalidArgumentException('Daemon '.$daemonName.' not found.');
        }
    }
}
