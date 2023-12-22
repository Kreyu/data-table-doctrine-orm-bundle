<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Tests\Fixtures\Filter\ExpressionTransformer;

use Kreyu\Bundle\DataTableBundle\Exception\BadMethodCallException;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\ExpressionTransformerInterface;

class CustomExpressionTransformer implements ExpressionTransformerInterface
{
    public function transform(mixed $expression): mixed
    {
        throw new BadMethodCallException('Not supported');
    }
}