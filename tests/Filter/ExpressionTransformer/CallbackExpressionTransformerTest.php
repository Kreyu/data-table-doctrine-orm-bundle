<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Tests\Filter\ExpressionTransformer;

use Doctrine\ORM\Query\Expr;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\CallbackExpressionTransformer;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\ExpressionTransformerInterface;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Test\ExpressionTransformerTestCase;

class CallbackExpressionTransformerTest extends ExpressionTransformerTestCase
{
    public static function createTransformer(callable $callback = null): ExpressionTransformerInterface
    {
        return new CallbackExpressionTransformer($callback);
    }

    public static function expressionTransformationProvider(): iterable
    {
        $expr = new Expr();

        yield [self::createTransformer(trim(...)), ' foo ', 'foo'];

        yield [self::createTransformer(static fn ($expression) => "$expression bar"), 'foo', 'foo bar'];

        yield [
            self::createTransformer(static function (Expr\Comparison $comparison) use ($expr) {
                return $expr->eq($expr->lower($comparison->getLeftExpr()), $expr->upper($comparison->getRightExpr()));
            }),
            $expr->eq('foo', 'bar'),
            $expr->eq($expr->lower('foo'), $expr->upper('bar')),
        ];
    }
}
