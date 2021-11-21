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
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class ActionExecuteCommand extends Command
{
    protected static $defaultName = 'sourecode:action:execute';

    private ActionDefinitionList $actionDefinitionList;

    private ActionRunner $actionRunner;

    public function __construct(ActionRunner $actionRunner, ActionDefinitionList $actionDefinitionList)
    {
        parent::__construct();
        $this->actionRunner = $actionRunner;
        $this->actionDefinitionList = $actionDefinitionList;
    }

    public function configure(): void
    {
        $this
            ->setAliases(['sourecode:action'])
            ->setDescription('Execute an action')
            ->setHelp('This command allows you to execute the given action.')
            ->addArgument('action', InputArgument::REQUIRED, 'The action to execute')
            ->addArgument('job', InputArgument::OPTIONAL, 'The job to execute');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $action = $input->getArgument('action');
        $job = $input->hasArgument('job') ? $input->getArgument('job') : null;

        if (!$this->actionDefinitionList->has($action)) {
            $actions = $this->actionDefinitionList->getActionDefinitions();
            $output->writeln(sprintf('<error>Action "%s" not found</error>', $action));

            $output->writeln('Available actions:');

            foreach ($actions as $action) {
                $output->writeln(sprintf(' - <info>%s</info>', $action->getName()));
            }

            return Command::INVALID;
        }

        $this->actionRunner->executeAction($output, $action, $job);

        return Command::SUCCESS;
    }
}
