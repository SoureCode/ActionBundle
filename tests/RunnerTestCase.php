<?php
/*
 * This file is part of the SoureCode package.
 *
 * (c) Jason Schilling <jason@sourecode.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace SoureCode\Bundle\Action\Tests;

use Symfony\Component\Console\Output\BufferedOutput;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
class RunnerTestCase extends AbstractActionTestCase
{
    public function testExecuteAction()
    {
        // Arrange
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();
        $runner = $container->get('soure_code.action.runner');

        $output = new BufferedOutput();
        $runner->executeAction($output, 'files');

        // Assert
        $this->assertEquals("composer.json\ncomposer.lock\nprefix_composer.json\nprefix_composer.lock\n", $output->fetch());
    }

    public function testExecuteActionAndJob()
    {
        // Arrange
        $kernel = self::bootKernel();
        $container = $kernel->getContainer();
        $runner = $container->get('soure_code.action.runner');

        $output = new BufferedOutput();
        $runner->executeAction($output, 'files', 'prefixed');

        // Assert
        $this->assertEquals("prefix_composer.json\nprefix_composer.lock\n", $output->fetch());
    }
}
