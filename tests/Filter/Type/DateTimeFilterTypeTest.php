<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Tests\Filter\Type;

use Doctrine\ORM\Query\Expr;
use Kreyu\Bundle\DataTableBundle\Filter\Operator;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\DateTimeFilterType;

class DateTimeFilterTypeTest extends DoctrineOrmFilterTypeTestCase
{
    public static function operatorExpressionProvider(): iterable
    {
        $expr = new Expr();
        $value = new \DateTime();

        yield [Operator::Equals, $expr->eq(...), $value];
        yield [Operator::NotEquals, $expr->neq(...), $value];
        yield [Operator::GreaterThan, $expr->gt(...), $value];
        yield [Operator::GreaterThanEquals, $expr->gte(...), $value];
        yield [Operator::LessThan, $expr->lt(...), $value];
        yield [Operator::LessThanEquals, $expr->lte(...), $value];
    }

    protected function getTestedType(): string
    {
        return DateTimeFilterType::class;
    }
}
