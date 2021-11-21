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
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class ActionListCommand extends Command
{
    protected static $defaultName = 'sourecode:action:list';

    private ActionDefinitionList $actionDefinitionList;

    public function __construct(ActionDefinitionList $actionDefinitionList)
    {
        parent::__construct();
        $this->actionDefinitionList = $actionDefinitionList;
    }

    public function configure(): void
    {
        $this
            ->setAliases(['sourecode:action:list'])
            ->setDescription('List all available actions')
            ->setHelp('This command allows you to list all available actions.')
            ->addArgument('action', InputArgument::OPTIONAL, 'The action to list');
    }

    public function execute(InputInterface $input, OutputInterface $output): int
    {
        $actionName = $input->hasArgument('action') ? $input->getArgument('action') : null;

        $exitCode = Command::SUCCESS;
        $printActions = true;
        if (null !== $actionName) {
            if (!$this->actionDefinitionList->has($actionName)) {
                $output->writeln(sprintf('<error>Action "%s" not found</error>', $actionName));

                $exitCode = Command::INVALID;
            } else {
                $printActions = false;

                $action = $this->actionDefinitionList->get($actionName);
                $dependencies = $action->getDependencies();
                $jobs = $action->getJobs();

                $output->writeln(sprintf('<info>Action "%s"</info>', $action->getName()));
                $output->writeln(sprintf('<comment>Dependencies:</comment> %s', implode(', ', $dependencies)));
                $output->writeln('<comment>Jobs:</comment>');

                foreach ($jobs as $job) {
                    $output->writeln(sprintf('  - %s', $job->getName()));
                    $output->writeln(sprintf('    Dependencies: %s', implode(', ', $job->getDependencies())));
                }
            }
        }

        if ($printActions) {
            $output->writeln('Available actions:');

            $actions = $this->actionDefinitionList->getActionDefinitions();

            foreach ($actions as $actionName) {
                $output->writeln(sprintf(' - <info>%s</info>', $actionName->getName()));
            }
        }

        return $exitCode;
    }
}
