<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Tests\Extension;

use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Extension\DoctrineOrmFilterExtension;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\BooleanFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\DateFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\DateRangeFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\DateTimeFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\DoctrineOrmFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\EntityFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\NumberFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\TextFilterType;
use PHPUnit\Framework\TestCase;

class DoctrineOrmFilterExtensionTest extends TestCase
{
    public function testItLoadsTypes(): void
    {
        $extension = new DoctrineOrmFilterExtension();

        $types = [
            DoctrineOrmFilterType::class,
            TextFilterType::class,
            NumberFilterType::class,
            BooleanFilterType::class,
            DateFilterType::class,
            DateTimeFilterType::class,
            DateRangeFilterType::class,
            EntityFilterType::class,
        ];

        foreach ($types as $type) {
            $this->assertTrue($extension->hasType($type));
            $this->assertInstanceOf($type, $extension->getType($type));
        }
    }
}
