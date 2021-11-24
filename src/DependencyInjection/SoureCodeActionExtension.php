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

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class SoureCodeActionExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container): void
    {
        $configuration = new Configuration();
        /**
         * @√ar array{actions: array<string, array{needs: list<string>, jobs: array<string, {needs: list<string>, continue_on_error: bool, tasks: array<string, {command: string, continue_on_error: bool, input: string, output: string, directory: string}>}>}>} $config
         */
        $config = $this->processConfiguration($configuration, $configs);
        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../../config'));

        $loader->load('services.php');

        $container->setParameter('soure_code.action.daemons', $config['daemons']);

        $commandDefinition = $container->getDefinition('soure_code.action.action_definitions');
        $commandDefinition->setArgument('$actions', $config['actions']);
    }
}
