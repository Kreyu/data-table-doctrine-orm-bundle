<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Tests\Filter\ExpressionTransformer;

use Doctrine\ORM\Query\Expr;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\ExpressionTransformerInterface;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\UpperExpressionTransformer;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Test\ExpressionTransformerTestCase;

class UpperExpressionTransformerTest extends ExpressionTransformerTestCase
{
    public static function createTransformer(bool $transformLeftExpr = true, bool $transformRightExpr = true): ExpressionTransformerInterface
    {
        return new UpperExpressionTransformer($transformLeftExpr, $transformRightExpr);
    }

    public static function expressionTransformationProvider(): iterable
    {
        $expr = new Expr();

        yield [self::createTransformer(), $expr->eq('a', 'b'), $expr->eq($expr->upper('a'), $expr->upper('b'))];

        yield [self::createTransformer(false), $expr->eq('a', 'b'), $expr->eq('a', $expr->upper('b'))];

        yield [self::createTransformer(true, false), $expr->eq('a', 'b'), $expr->eq($expr->upper('a'), 'b')];

        yield [self::createTransformer(false, false), $expr->eq('a', 'b'), $expr->eq('a', 'b')];
    }
}
