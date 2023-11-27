<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer;

interface ExpressionTransformerInterface
{
    public function transform(mixed $expression): mixed;
}
