<?php
/*
 * This file is part of the SoureCode package.
 *
 * (c) Jason Schilling <jason@sourecode.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SoureCode\Bundle\Action\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('soure_code_action');

        /**
         * @var ArrayNodeDefinition $rootNode
         */
        $rootNode = $treeBuilder->getRootNode();

        $children = $rootNode
            ->fixXmlConfig('action')
            ->children();

        $actionNode = $children
            ->arrayNode('actions')
            ->useAttributeAsKey('name')
            ->arrayPrototype();

        $actionChildren = $actionNode
            ->fixXmlConfig('job')
            ->children();

        $actionChildren
            ->arrayNode('needs')
            ->scalarPrototype()
            ->defaultValue([]);

        $jobsNode = $actionChildren
            ->arrayNode('jobs')
            ->useAttributeAsKey('name')
            ->arrayPrototype();

        $jobsChildren = $jobsNode
            ->fixXmlConfig('task')
            ->children();

        $jobsChildren
            ->arrayNode('needs')
            ->scalarPrototype();

        $jobsChildren->booleanNode('continue_on_error')
            ->defaultValue(false);

        $tasksNode = $jobsChildren
            ->arrayNode('tasks')
            ->useAttributeAsKey('name')
            ->arrayPrototype();

        $tasksChildren = $tasksNode
            ->addDefaultsIfNotSet()
            ->children();

        $tasksChildren->scalarNode('command')
            ->isRequired()
            ->cannotBeEmpty();

        $tasksChildren->scalarNode('directory')
            ->defaultValue(getcwd())
            ->validate()
            ->ifTrue(function (string $directory) {
                return !is_dir($directory) || !file_exists($directory);
            })
            ->thenInvalid('%s is not a valid directory');

        $tasksChildren->scalarNode('name')
            ->defaultNull();

        $tasksChildren->scalarNode('output')
            ->defaultNull();

        $tasksChildren->arrayNode('inputs')
            ->scalarPrototype()
            ->defaultValue([]);

        $tasksChildren->booleanNode('continue_on_error')
            ->defaultValue(false);

        $tasksNode->validate()
            ->ifTrue(function (array $task) {
                return !$this->validateTask($task);
            })
            ->thenInvalid('Invalid task configuration.');

        return $treeBuilder;
    }

    private function validateTask(array $task): bool
    {
        // @todo validate task
        return true;
    }
}
