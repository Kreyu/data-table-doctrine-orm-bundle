<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Tests\Filter\Type;

use Doctrine\ORM\Query\Expr;
use Kreyu\Bundle\DataTableBundle\Filter\Operator;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\BooleanFilterType;

class BooleanFilterTypeTest extends DoctrineOrmFilterTypeTestCase
{
    public static function operatorExpressionProvider(): iterable
    {
        $expr = new Expr();

        yield [Operator::Equals, $expr->eq(...)];
        yield [Operator::NotEquals, $expr->neq(...)];
    }

    protected function getTestedType(): string
    {
        return BooleanFilterType::class;
    }
}
