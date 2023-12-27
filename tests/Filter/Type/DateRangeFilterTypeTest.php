<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Tests\Filter\Type;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\Form\Type\DateRangeType;
use Kreyu\Bundle\DataTableBundle\Filter\Operator;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\DateRangeFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Query\DoctrineOrmProxyQuery;
use PHPUnit\Framework\Attributes\DataProvider;

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
