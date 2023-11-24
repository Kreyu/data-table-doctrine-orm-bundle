<?php

declare(strict_types=1);

namespace Kreyu\Bridge\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer;

interface ExpressionTransformerInterface
{
    public function transform(mixed $expression): mixed;
}
