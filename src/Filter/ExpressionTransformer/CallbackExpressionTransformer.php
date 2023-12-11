<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer;

class CallbackExpressionTransformer implements ExpressionTransformerInterface
{
    private readonly \Closure $callback;

    public function __construct(callable $callback)
    {
        $this->callback = $callback(...);
    }

    public function transform(mixed $expression): mixed
    {
        return ($this->callback)($expression);
    }
}
