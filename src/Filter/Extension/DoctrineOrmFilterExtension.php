<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Extension;

use Kreyu\Bundle\DataTableBundle\Filter\Extension\AbstractFilterExtension;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\BooleanFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\DateFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\DateRangeFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\DateTimeFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\DoctrineOrmFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\EntityFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\NumberFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\TextFilterType;

class DoctrineOrmFilterExtension extends AbstractFilterExtension
{
    protected function loadTypes(): array
    {
        return [
            new DoctrineOrmFilterType(),
            new TextFilterType(),
            new NumberFilterType(),
            new BooleanFilterType(),
            new DateFilterType(),
            new DateTimeFilterType(),
            new DateRangeFilterType(),
            new EntityFilterType(),
        ];
    }
}
