<?php
/*
 * This file is part of the SoureCode package.
 *
 * (c) Jason Schilling <jason@sourecode.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SoureCode\Bundle\Action\Factory;

use SoureCode\Component\Action\ActionDefinition;
use SoureCode\Component\Action\ActionDefinitionList;
use SoureCode\Component\Action\JobDefinition;
use SoureCode\Component\Action\TaskDefinition;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class ActionDefinitionsListFactory
{
    /**
     * @param array<string, array{needs: list<string>, jobs: array<string, array{needs: list<string>, continue_on_error: bool, tasks: array<string, array{command: string, continue_on_error: bool, input: string, output: string, directory: string}>}>}> $actions
     */
    public function createActionDefinitionsList(array $actions): ActionDefinitionList
    {
        $actionDefinitions = [];

        foreach ($actions as $name => $action) {
            $actionDefinition = new ActionDefinition($name);

            $actionDefinition->setDependencies($action['needs']);
            $actionDefinition->setJobs($this->createJobDefinitions($action['jobs']));

            $actionDefinitions[$name] = $actionDefinition;
        }

        return new ActionDefinitionList($actionDefinitions);
    }

    /**
     * @param array<string, array{needs: list<string>, continue_on_error: bool, tasks: array<string, array{command: string, continue_on_error: bool, input: string, output: string, directory: string}>}> $jobs
     *
     * @return array<string, JobDefinition>
     */
    private function createJobDefinitions(array $jobs): array
    {
        $jobDefinitions = [];

        foreach ($jobs as $name => $job) {
            $jobDefinition = new JobDefinition($name);

            $jobDefinition->setContinueOnError($job['continue_on_error']);
            $jobDefinition->setDependencies($job['needs']);
            $jobDefinition->setTasks($this->createTaskDefinitions($job['tasks']));

            $jobDefinitions[$name] = $jobDefinition;
        }

        return $jobDefinitions;
    }

    /**
     * @param array<string, array{command: string, continue_on_error: bool, input: string, output: string, directory: string}> $tasks
     *
     * @return array<string, TaskDefinition>
     */
    private function createTaskDefinitions(array $tasks): array
    {
        $taskDefinitions = [];

        foreach ($tasks as $name => $task) {
            $taskDefinition = new TaskDefinition($name, $task['command']);

            $taskDefinition->setContinueOnError($task['continue_on_error']);
            $taskDefinition->setInputKey($task['input']);
            $taskDefinition->setOutputKey($task['output']);
            $taskDefinition->setDirectory($task['directory']);

            $taskDefinitions[$name] = $taskDefinition;
        }

        return $taskDefinitions;
    }
}
