<?php
/*
 * This file is part of the SoureCode package.
 *
 * (c) Jason Schilling <jason@sourecode.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

use SoureCode\Bundle\Action\Command\ActionDaemonRunCommand;
use SoureCode\Bundle\Action\Command\ActionDaemonStartCommand;
use SoureCode\Bundle\Action\Command\ActionDaemonStopCommand;
use SoureCode\Bundle\Action\Command\ActionExecuteCommand;
use SoureCode\Bundle\Action\Command\ActionListCommand;
use SoureCode\Bundle\Action\Factory\ActionDefinitionsListFactory;
use SoureCode\Component\Action\ActionDefinitionList;
use SoureCode\Component\Action\ActionFactory;
use SoureCode\Component\Action\ActionRunner;
use SoureCode\Component\Action\JobFactory;
use SoureCode\Component\Action\MemoryStorage;
use SoureCode\Component\Action\StorageInterface;
use SoureCode\Component\Action\TaskFactory;
use function Symfony\Component\DependencyInjection\Loader\Configurator\abstract_arg;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container) {
    $services = $container->services();

    $services
        ->set('soure_code.action.storage', MemoryStorage::class)
        ->alias(StorageInterface::class, 'soure_code.action.storage');

    $services->set('soure_code.action.factory.task', TaskFactory::class);

    $services->set('soure_code.action.factory.job', JobFactory::class)
        ->args([
            service('soure_code.action.storage'),
            service('soure_code.action.factory.task'),
        ]);

    $services->set('soure_code.action.factory.action', ActionFactory::class)
        ->args([
            service('soure_code.action.factory.job'),
        ]);

    $services->set('soure_code.action.factory.action_definitions', ActionDefinitionsListFactory::class);

    $services->set('soure_code.action.action_definitions', ActionDefinitionList::class)
        ->factory([service('soure_code.action.factory.action_definitions'), 'createActionDefinitionsList'])
        ->args([
            abstract_arg('$actions'),
        ]);

    $services->set('soure_code.action.runner', ActionRunner::class)
        ->args([
            service('soure_code.action.factory.action'),
            service('soure_code.action.action_definitions'),
        ])
        ->public();

    $services
        ->set('soure_code.action.command.execute', ActionExecuteCommand::class)
        ->tag('console.command', ['command' => 'sourecode:action:execute'])
        ->tag('console.command', ['command' => 'sourecode:action'])
        ->args([
            service('soure_code.action.runner'),
            service('soure_code.action.action_definitions'),
        ])
        ->alias(ActionExecuteCommand::class, 'soure_code.action.command.execute');

    $services
        ->set('soure_code.action.command.list', ActionListCommand::class)
        ->tag('console.command', ['command' => 'sourecode:action:list'])
        ->args([
            service('soure_code.action.action_definitions'),
        ])
        ->alias(ActionListCommand::class, 'soure_code.action.command.list');

    $services
        ->set('soure_code.action.command.daemon_run', ActionDaemonRunCommand::class)
        ->tag('console.command', ['command' => 'sourecode:daemon:run'])
        ->args([
            service('filesystem'),
            param('kernel.project_dir'),
            param('kernel.logs_dir'),
            param('soure_code.action.daemons'),
        ])
        ->alias(ActionDaemonRunCommand::class, 'soure_code.action.command.daemon_run')
        ->public();

    $services
        ->set('soure_code.action.command.daemon_start', ActionDaemonStartCommand::class)
        ->tag('console.command', ['command' => 'sourecode:daemon:start'])
        ->args([
            service('filesystem'),
            param('kernel.project_dir'),
            param('kernel.logs_dir'),
            param('soure_code.action.daemons'),
        ])
        ->alias(ActionDaemonStartCommand::class, 'soure_code.action.command.daemon_start')
        ->public();

    $services
        ->set('soure_code.action.command.daemon_stop', ActionDaemonStopCommand::class)
        ->tag('console.command', ['command' => 'sourecode:daemon:stop'])
        ->args([
            service('filesystem'),
            param('kernel.project_dir'),
            param('kernel.logs_dir'),
            param('soure_code.action.daemons'),
        ])
        ->alias(ActionDaemonStopCommand::class, 'soure_code.action.command.daemon_stop')
        ->public();
};
