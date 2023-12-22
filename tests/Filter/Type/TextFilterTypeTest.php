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

        yield 'equals' => [Operator::Equals, $expr->eq(...)];
        yield 'not equals' => [Operator::NotEquals, $expr->neq(...)];
        yield 'contains' => [Operator::Contains, $expr->like(...)];
        yield 'not contains' => [Operator::NotContains, $expr->notLike(...)];
        yield 'starts with' => [Operator::StartsWith, $expr->like(...)];
        yield 'ends with' => [Operator::EndsWith, $expr->like(...)];
    }

    protected function getTestedType(): string
    {
        return TextFilterType::class;
    }
}
