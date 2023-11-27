<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Kreyu\Bundle\DataTableBundle\Exception\InvalidArgumentException;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableBundle\Filter\Operator;
use Kreyu\Bundle\DataTableBundle\Filter\Type\AbstractFilterType;
use Kreyu\Bundle\DataTableBundle\Query\ProxyQueryInterface;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\ExpressionTransformerInterface;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\LowerExpressionTransformer;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\TrimExpressionTransformer;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\UpperExpressionTransformer;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Query\DoctrineOrmProxyQueryInterface;

abstract class AbstractDoctrineOrmFilterType extends AbstractFilterType
{
    protected array $expressionTransformerOptionMap = [
        'trim' => TrimExpressionTransformer::class,
        'upper' => UpperExpressionTransformer::class,
        'lower' => LowerExpressionTransformer::class,
    ];

    public function apply(ProxyQueryInterface $query, FilterData $data, FilterInterface $filter, array $options): void
    {
        if (!$query instanceof DoctrineOrmProxyQueryInterface) {
            throw new InvalidArgumentException(sprintf('Query must be an instance of "%s"', DoctrineOrmProxyQueryInterface::class));
        }

        $queryBuilder = $query->getQueryBuilder();

        $operator = $this->getFilterOperator($data, $filter);
        $value = $this->getFilterValue($data);

        if (!in_array($operator, $filter->getConfig()->getSupportedOperators())) {
            return;
        }

        $queryPath = $this->getFilterQueryPath($queryBuilder, $filter);

        $parameterName = $this->getUniqueParameterName($query, $filter);
        $parameterValue = $this->getParameterValue($operator, $value);

        try {
            $expression = $this->getOperatorExpression($queryPath, $parameterName, $operator, new Expr());
        } catch (InvalidArgumentException) {
            return;
        }

        $expressionTransformers = $this->getExpressionTransformers($options);

        $expression = $this->applyExpressionTransformers($expressionTransformers, $expression);

        $this->applyExpression($queryBuilder, $expression, $parameterName, $parameterValue);
    }

    public function getParent(): ?string
    {
        return DoctrineOrmFilterType::class;
    }

    protected function getUniqueParameterName(DoctrineOrmProxyQueryInterface $query, FilterInterface $filter): string
    {
        return $filter->getFormName().'_'.$query->getUniqueParameterId();
    }

    protected function getFilterOperator(FilterData $data, FilterInterface $filter): Operator
    {
        return $data->getOperator() ?? $filter->getConfig()->getDefaultOperator();
    }

    protected function getFilterValue(FilterData $data): mixed
    {
        return $data->getValue();
    }

    protected function getFilterQueryPath(QueryBuilder $queryBuilder, FilterInterface $filter): string
    {
        $rootAlias = current($queryBuilder->getRootAliases());

        $queryPath = $filter->getQueryPath();

        if ($rootAlias && !str_contains($queryPath, '.') && $filter->getConfig()->getOption('auto_alias_resolving')) {
            $queryPath = $rootAlias.'.'.$queryPath;
        }

        return $queryPath;
    }

    /**
     * @throws InvalidArgumentException if operator is not supported by the filter
     */
    protected function getOperatorExpression(string $queryPath, string $parameterName, Operator $operator, Expr $expr): object
    {
        throw new InvalidArgumentException('Operator not supported');
    }

    /**
     * @return iterable<ExpressionTransformerInterface> $expressionTransformers
     */
    protected function getExpressionTransformers(array $options): iterable
    {
        foreach ($this->expressionTransformerOptionMap as $option => $expressionTransformerClass) {
            if ($options[$option]) {
                yield (new $expressionTransformerClass())();
            }
        }

        if ($expressionTransformer = $options['expression_transformer']) {
            yield $expressionTransformer;
        }
    }

    /**
     * @param iterable<ExpressionTransformerInterface> $expressionTransformers
     */
    protected function applyExpressionTransformers(iterable $expressionTransformers, mixed $expression): mixed
    {
        foreach ($expressionTransformers as $expressionTransformer) {
            $expression = $expressionTransformer->transform($expression);
        }

        return $expression;
    }

    protected function getParameterValue(Operator $operator, mixed $value): mixed
    {
        return $value;
    }

    protected function applyExpression(QueryBuilder $queryBuilder, mixed $expression, string $parameterName, mixed $parameterValue): void
    {
        $queryBuilder->andWhere($expression)->setParameter($parameterName, $parameterValue);
    }
}
