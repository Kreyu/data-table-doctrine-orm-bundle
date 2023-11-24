<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Comparison;
use Kreyu\Bundle\DataTableBundle\Exception\UnexpectedTypeException;

abstract class AbstractComparisonExpressionTransformer implements ExpressionTransformerInterface
{
    public function __construct(
        private readonly bool $transformLeftExpr = true,
        private readonly bool $transformRightExpr = true,
    ) {
    }

    public function transform(mixed $expression): Comparison
    {
        if (!$expression instanceof Comparison) {
            throw new UnexpectedTypeException($expression, Comparison::class);
        }

        $expr = new Expr();

        $leftExpr = $expression->getLeftExpr();
        $rightExpr = $expression->getRightExpr();

        if ($this->transformLeftExpr) {
            $leftExpr = $this->transformLeftExpr($leftExpr, $expr);
        }

        if ($this->transformRightExpr) {
            $rightExpr = $this->transformRightExpr($rightExpr, $expr);
        }

        return new Comparison($leftExpr, $expression->getOperator(), $rightExpr);
    }

    protected function transformLeftExpr(mixed $leftExpr, Expr $expr): mixed
    {
        return $leftExpr;
    }

    protected function transformRightExpr(mixed $rightExpr, Expr $expr): mixed
    {
        return $rightExpr;
    }
}