<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer;

use Kreyu\Bundle\DataTableBundle\Exception\UnexpectedTypeException;

class ChainExpressionTransformer implements ExpressionTransformerInterface
{
    /**
     * @param iterable<ExpressionTransformerInterface> $expressionTransformers
     */
    public function __construct(
        private readonly iterable $expressionTransformers = [],
    ) {
        foreach ($expressionTransformers as $expressionTransformer) {
            if (!$expressionTransformer instanceof ExpressionTransformerInterface) {
                throw new UnexpectedTypeException($expressionTransformer, ExpressionTransformerInterface::class);
            }
        }
    }

    public function transform(mixed $expression): mixed
    {
        foreach ($this->expressionTransformers as $transformer) {
            $expression = $transformer->transform($expression);
        }

        return $expression;
    }
}
