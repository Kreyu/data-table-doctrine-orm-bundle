<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Tests\Filter\Type;

use Doctrine\ORM\Query\Expr;
use Kreyu\Bundle\DataTableBundle\Filter\Operator;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\NumberFilterType;

class NumberFilterTypeTest extends DoctrineOrmFilterTypeTestCase
{
    public static function operatorExpressionProvider(): iterable
    {
        $expr = new Expr();

        yield [Operator::Equals, $expr->eq(...)];
        yield [Operator::NotEquals, $expr->neq(...)];
        yield [Operator::GreaterThanEquals, $expr->gte(...)];
        yield [Operator::GreaterThan, $expr->gt(...)];
        yield [Operator::LessThanEquals, $expr->lte(...)];
        yield [Operator::LessThan, $expr->lt(...)];
    }

    protected function getTestedType(): string
    {
        return NumberFilterType::class;
    }
}
