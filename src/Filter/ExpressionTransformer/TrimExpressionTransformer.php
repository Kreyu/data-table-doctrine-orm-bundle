<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer;

use Doctrine\ORM\Query\Expr;

class TrimExpressionTransformer extends AbstractComparisonExpressionTransformer
{
    protected function transformLeftExpr(mixed $leftExpr, Expr $expr): Expr\Func
    {
        return $expr->trim($leftExpr);
    }

    protected function transformRightExpr(mixed $rightExpr, Expr $expr): Expr\Func
    {
        return $expr->trim($rightExpr);
    }
}
