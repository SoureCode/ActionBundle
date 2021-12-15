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

use SoureCode\Component\Action\ActionDefinitionList;
use SoureCode\Component\Action\ActionRunner;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class ActionExecuteCommand extends Command
{
    protected static $defaultName = 'sourecode:action';

    private ActionDefinitionList $actionDefinitionList;

    private ActionRunner $actionRunner;

    public function __construct(ActionRunner $actionRunner, ActionDefinitionList $actionDefinitionList, string $name = null)
    {
        parent::__construct($name);
        $this->actionRunner = $actionRunner;
        $this->actionDefinitionList = $actionDefinitionList;
    }

    public function configure(): void
    {
        $this
            ->setDescription('Execute an action')
            ->setHelp('This command allows you to execute the given action.')
            ->addArgument('action', InputArgument::OPTIONAL, 'The action to execute')
            ->addArgument('job', InputArgument::OPTIONAL, 'The job to execute')
            ->addOption('--list', '-l', InputOption::VALUE_NONE, 'List all available actions');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        if ($input->getOption('list')) {
            return $this->listActions($io);
        }

        $actionName = $input->getArgument('action');

        if (!$actionName) {
            $io->error('Missing argument "action".');

            return Command::FAILURE;
        }

        if (!$this->actionDefinitionList->has($actionName)) {
            $io->error(sprintf('Action "%s" not found.', $actionName));

            $this->listActions($io);

            return Command::FAILURE;
        }

        $job = $input->hasArgument('job') ? $input->getArgument('job') : null;

        $this->actionRunner->executeAction($output, $actionName, $job);

        return Command::SUCCESS;
    }

    private function listActions(SymfonyStyle $io): int
    {
        $io->title('Available actions and jobs:');

        $actions = $this->actionDefinitionList->getActionDefinitions();

        foreach ($actions as $action) {
            $jobs = $action->getJobs();
            $dependencies = $action->getDependencies();
            $needs = \count($dependencies) > 0 ? sprintf(' (needs: %s)', implode(', ', $dependencies)) : '';

            $io->writeln(
                sprintf(' * %s%s', $action->getName(), $needs)
            );

            if (\count($jobs) > 0) {
                foreach ($jobs as $job) {
                    $dependencies = $job->getDependencies();
                    $needs = \count($dependencies) > 0 ? sprintf(' (needs: %s)', implode(', ', $dependencies)) : '';

                    $io->writeln(sprintf('    * %s%s', $job->getName(), $needs));
                }
            }
        }

        return Command::SUCCESS;
    }
}
