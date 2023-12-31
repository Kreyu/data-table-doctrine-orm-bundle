<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Tests;

use DG\BypassFinals;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\KreyuDataTableDoctrineOrmBundle;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

class KreyuDataTableDoctrineOrmBundleTest extends KernelTestCase
{
    public function testLoadExtension()
    {
        BypassFinals::enable();

        $containerConfigurator = $this->createMock(ContainerConfigurator::class);

        /* @noinspection PhpUnitInvalidMockingEntityInspection */
        $containerConfigurator->expects($this->once())->method('import');

        $bundle = new KreyuDataTableDoctrineOrmBundle();
        $bundle->loadExtension([], $containerConfigurator, $this->createMock(ContainerBuilder::class));
    }
}
