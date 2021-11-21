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

use Nyholm\BundleTest\TestKernel;
use SoureCode\Bundle\Action\SoureCodeActionBundle;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Bundle\MonologBundle\MonologBundle;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * @author Jason Schilling <jason@sourecode.dev>
 */
abstract class AbstractActionTestCase extends KernelTestCase
{
    protected static function createKernel(array $options = []): KernelInterface
    {
        /**
         * @var TestKernel $kernel
         */
        $kernel = parent::createKernel($options);
        $kernel->addTestBundle(SoureCodeActionBundle::class);
        $kernel->addTestBundle(MonologBundle::class);
        // $kernel->setTestProjectDir(__DIR__.'/App');
        $kernel->addTestConfig(__DIR__.'/config.yml');
        $kernel->handleOptions($options);

        return $kernel;
    }

    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }
}
