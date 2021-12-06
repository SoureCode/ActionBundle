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

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\Process\Process;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class ActionDaemonRunCommand extends AbstractActionDaemonCommand
{
    protected static $defaultName = 'sourecode:daemon:run';

    public function __construct(Filesystem $filesystem, string $projectDirectory, string $logsDirectory, array $daemons)
    {
        parent::__construct();
        $this->projectDirectory = $projectDirectory;
        $this->filesystem = $filesystem;
        $this->logsDirectory = $logsDirectory;
        $this->daemons = $daemons;
    }

    public function configure(): void
    {
        $this
            ->setAliases(['sourecode:daemon:run'])
            ->setDescription('Starts a daemon')
            ->setHelp('This command allows you to run the given daemon.')
            ->addArgument('name', InputArgument::REQUIRED, 'The daemon to run');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $daemonName = $input->getArgument('name');

        $this->validateDaemonName($daemonName);

        $pidFilepath = $this->getPidFilepath($daemonName);

        if ($this->isDaemonRunning($daemonName)) {
            $output->writeln('Process already running.');

            return Command::FAILURE;
        }

        $process = Process::fromShellCommandline(
            'exec '.$this->daemons[$daemonName]['command'],
            null,
            ['APP_ENV' => false, 'SYMFONY_DOTENV_VARS' => false],
            null,
            null,
        );

        $process->start();

        $pid = $process->getPid();
        $this->filesystem->dumpFile($pidFilepath, (string) $pid);

        $stdout = $this->getLogsStdoutFilepath($daemonName);
        $stderr = $this->getLogsErroutFilepath($daemonName);

        foreach ($process as $type => $data) {
            if (Process::OUT === $type) {
                $this->filesystem->appendToFile($stdout, $data);
            } else {
                if (Process::ERR === $type) {
                    $this->filesystem->appendToFile($stderr, $data);
                }
            }
        }

        $this->filesystem->remove($pidFilepath);

        return $process->wait();
    }
}
