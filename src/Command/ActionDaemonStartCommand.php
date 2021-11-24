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
class ActionDaemonStartCommand extends AbstractActionDaemonCommand
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
            ->setDescription('Starts a daemon')
            ->setHelp('This command allows you to start the given daemon.')
            ->addArgument('name', InputArgument::REQUIRED, 'The daemon to start');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $daemonName = $input->getArgument('name');

        $this->validateDaemonName($daemonName);

        if ($this->isDaemonRunning($daemonName)) {
            $output->writeln('Process already running.');

            return Command::FAILURE;
        }

        (int) shell_exec(
            sprintf('%s > /dev/null 2>&1 & echo $!', 'exec php bin/console sourecode:daemon:run '.$daemonName)
        );

        return Command::SUCCESS;
    }
}
