<?php

declare(strict_types=1);

namespace Kreyu\Bridge\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer;

use Doctrine\ORM\Query\Expr\Comparison;
use Kreyu\Bundle\DataTableBundle\Exception\UnexpectedTypeException;

class LowerExpressionTransformer implements ExpressionTransformerInterface
{
    public function __construct(
        private readonly bool $lowerLeft = true,
        private readonly bool $lowerRight = true
    ) {
    }

    public function transform(mixed $expression): Comparison
    {
        if (!$expression instanceof Comparison) {
            throw new UnexpectedTypeException($expression, Comparison::class);
        }

        $leftExpr = $expression->getLeftExpr();
        $rightExpr = $expression->getRightExpr();

        if ($this->lowerLeft) {
            $leftExpr = "LOWER($leftExpr)";
        }

        if ($this->lowerRight) {
            $rightExpr = "LOWER($rightExpr)";
        }

        return new Comparison($leftExpr, $expression->getOperator(), $rightExpr);
    }
}
