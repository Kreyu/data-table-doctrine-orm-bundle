<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Tests\Fixtures\Filter\ExpressionTransformer;

use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\AbstractComparisonExpressionTransformer;

class CustomExpressionTransformer extends AbstractComparisonExpressionTransformer
{
    protected function transformLeftExpr(mixed $leftExpr): string
    {
        return sprintf('CUSTOM(%s)', $leftExpr);
    }

    protected function transformRightExpr(mixed $rightExpr): string
    {
        return sprintf('CUSTOM(%s)', $rightExpr);
    }
}
