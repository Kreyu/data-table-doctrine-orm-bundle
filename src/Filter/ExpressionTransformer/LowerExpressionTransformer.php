<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer;

use Doctrine\ORM\Query\Expr;

class LowerExpressionTransformer extends AbstractComparisonExpressionTransformer
{
    protected function transformLeftExpr(mixed $leftExpr, Expr $expr): Expr\Func
    {
        return $expr->lower($leftExpr);
    }

    protected function transformRightExpr(mixed $rightExpr, Expr $expr): Expr\Func
    {
        return $expr->lower($rightExpr);
    }
}
