<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter;

use Doctrine\ORM\Query\Expr;
use Kreyu\Bundle\DataTableBundle\Exception\UnexpectedTypeException;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterHandlerInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableBundle\Query\ProxyQueryInterface;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\LowerExpressionTransformer;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\TrimExpressionTransformer;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\UpperExpressionTransformer;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Query\DoctrineOrmProxyQueryInterface;

class DoctrineOrmFilterHandler implements FilterHandlerInterface
{
    private readonly \Closure $comparisonFactory;
    private readonly \Closure $parameterValueTransformer;

    /**
     * @param callable(FilterData $data, Expr $expr): Expr\Comparison $comparisonFactory
     * @param callable(FilterData $data): mixed $parameterValueTransformer
     */
    public function __construct(callable $comparisonFactory, callable $parameterValueTransformer)
    {
        $this->comparisonFactory = $comparisonFactory(...);
        $this->parameterValueTransformer = $parameterValueTransformer(...);
    }

    public function handle(ProxyQueryInterface $query, FilterData $data, FilterInterface $filter): void
    {
        if (!$query instanceof DoctrineOrmProxyQueryInterface) {
            throw new UnexpectedTypeException($query, DoctrineOrmProxyQueryInterface::class);
        }

        $queryBuilder = $query->getQueryBuilder();

        $operator = $data->getOperator() ?? $filter->getConfig()->getDefaultOperator();

        if (!in_array($operator, $filter->getConfig()->getSupportedOperators())) {
            return;
        }

        $rootAlias = current($queryBuilder->getRootAliases());

        $queryPath = $filter->getQueryPath($query);

        // TODO
        if ($rootAlias && !str_contains($queryPath, '.') && $filter->getConfig()->getOption('auto_alias_resolving')) {
            $queryPath = $rootAlias.'.'.$queryPath;
        }

        $parameterName = $filter->getFormName().'_'.$query->getUniqueParameterId();

        $comparison = ($this->comparisonFactory)($data, $queryPath, new Expr());

        $expression = $comparison($queryPath, ":$parameterName");

        $expression = $this->applyExpressionTransformers($expression, $filter);

        $queryBuilder
            ->andWhere($expression)
            ->setParameter($parameterName, ($this->parameterValueTransformer)($data))
        ;
    }

    private function applyExpressionTransformers(mixed $expression, FilterInterface $filter): mixed
    {
        $expressionTransformers = (array) $filter->getConfig()->getOption('expression_transformers');

        if ($filter->getConfig()->getOption('trim')) {
            array_unshift($expressionTransformers, new TrimExpressionTransformer());
        }

        if ($filter->getConfig()->getOption('lower')) {
            array_unshift($expressionTransformers, new LowerExpressionTransformer());
        }

        if ($filter->getConfig()->getOption('upper')) {
            array_unshift($expressionTransformers, new UpperExpressionTransformer());
        }

        foreach ($expressionTransformers as $expressionTransformer) {
            $expression = $expressionTransformer($expression);
        }

        return $expression;
    }
}
