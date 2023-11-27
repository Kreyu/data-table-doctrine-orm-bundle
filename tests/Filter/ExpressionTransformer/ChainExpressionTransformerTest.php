<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Tests\Filter\ExpressionTransformer;

use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\ChainExpressionTransformer;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\ExpressionTransformerInterface;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Test\ExpressionTransformerTestCase;

class ChainExpressionTransformerTest extends ExpressionTransformerTestCase
{
    public static function createTransformer(iterable $expressionTransformers = []): ExpressionTransformerInterface
    {
        return new ChainExpressionTransformer($expressionTransformers);
    }

    public static function expressionTransformationProvider(): iterable
    {
        $expressionTransformerBar = new TestExpressionTransformer('bar');
        $expressionTransformerBaz = new TestExpressionTransformer('baz');

        yield [self::createTransformer(), 'foo', 'foo'];

        yield [self::createTransformer([$expressionTransformerBar]), 'foo', 'foo bar'];

        yield [self::createTransformer([$expressionTransformerBar, $expressionTransformerBaz]), 'foo', 'foo bar baz'];
    }
}

class TestExpressionTransformer implements ExpressionTransformerInterface
{
    public function __construct(
        private readonly string $value,
    ) {
    }

    public function transform(mixed $expression): string
    {
        return $expression.' '.$this->value;
    }
}
