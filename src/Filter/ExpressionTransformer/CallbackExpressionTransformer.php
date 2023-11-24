<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer;

class CallbackExpressionTransformer implements ExpressionTransformerInterface
{
    public function __construct(
        private readonly \Closure $callback,
    ) {
    }

    public function transform(mixed $expression): mixed
    {
        return ($this->callback)($expression);
    }
}
