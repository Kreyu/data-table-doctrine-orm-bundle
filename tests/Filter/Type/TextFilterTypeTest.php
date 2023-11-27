<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Tests\Filter\Type;

use Doctrine\ORM\Query\Expr;
use Kreyu\Bundle\DataTableBundle\Filter\Operator;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\TextFilterType;

class TextFilterTypeTest extends DoctrineOrmFilterTypeTestCase
{
    public static function operatorExpressionProvider(): iterable
    {
        $expr = new Expr();

        yield [Operator::Equals, $expr->eq(...)];
        yield [Operator::NotEquals, $expr->neq(...)];
        yield [Operator::Contains, $expr->like(...)];
        yield [Operator::StartsWith, $expr->like(...)];
        yield [Operator::EndsWith, $expr->like(...)];
        yield [Operator::NotContains, $expr->notLike(...)];
    }

    protected function getTestedType(): string
    {
        return TextFilterType::class;
    }
}
