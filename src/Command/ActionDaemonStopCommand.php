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

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class ActionDaemonStopCommand extends AbstractActionDaemonCommand
{
    protected static $defaultName = 'sourecode:daemon:start';

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
            ->setAliases(['sourecode:daemon:start'])
            ->setDescription('Stops a daemon')
            ->setHelp('This command allows you to stop the given daemon.')
            ->addArgument('name', InputArgument::REQUIRED, 'The daemon to stop');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $daemonName = $input->getArgument('name');

        $this->validateDaemonName($daemonName);

        if (!$this->isDaemonRunning($daemonName)) {
            $output->writeln('Process not running.');

            return Command::FAILURE;
        }

        $pid = file_get_contents($this->getPidFilepath($daemonName));
        shell_exec(sprintf('kill %d 2>&1', $pid));

        return Command::SUCCESS;
    }
}
