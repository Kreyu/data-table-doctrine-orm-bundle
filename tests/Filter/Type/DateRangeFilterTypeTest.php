<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Tests\Filter\Type;

use Kreyu\Bundle\DataTableBundle\Filter\Form\Type\DateRangeType;
use Kreyu\Bundle\DataTableBundle\Filter\Operator;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\DateRangeFilterType;

class DateRangeFilterTypeTest extends DoctrineOrmFilterTypeTestCase
{
    protected function getTestedType(): string
    {
        return DateRangeFilterType::class;
    }

    protected function getDefaultOperator(): Operator
    {
        return Operator::Between;
    }

    protected function getSupportedOperators(): array
    {
        return [
            Operator::Between,
        ];
    }

    protected function getDefaultFormType(): string
    {
        return DateRangeType::class;
    }
}
