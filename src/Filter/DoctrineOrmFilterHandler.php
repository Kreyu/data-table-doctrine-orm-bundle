<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter;

use Doctrine\ORM\Query\Expr;
use Kreyu\Bundle\DataTableBundle\Exception\UnexpectedTypeException;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterHandlerInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableBundle\Query\ProxyQueryInterface;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Event\DoctrineOrmFilterEvent;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Event\DoctrineOrmFilterEvents;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Event\PreApplyExpressionEvent;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Event\PreSetParameterEvent;
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

        $operator = $data->getOperator() ?? $filter->getConfig()->getDefaultOperator();

        if (!in_array($operator, $filter->getConfig()->getSupportedOperators())) {
            return;
        }

        $queryBuilder = $query->getQueryBuilder();

        $queryPath = $query->getAliasResolver()->resolve($filter->getQueryPath(), $queryBuilder);

        $parameterName = $filter->getFormName().'_'.$query->getUniqueParameterId();

        $expression = $this->createExpression($data, $queryPath, $parameterName);

        $event = new PreApplyExpressionEvent($filter, $query, $data, $expression);

        $this->dispatch(DoctrineOrmFilterEvents::PRE_APPLY_EXPRESSION, $event);

        $queryBuilder->andWhere($event->getExpression());

        $parameterValue = $this->getParameterValue($data);

        $event = new PreSetParameterEvent($filter, $query, $data, $parameterName, $parameterValue);

        $this->dispatch(DoctrineOrmFilterEvents::PRE_SET_PARAMETER, $event);

        $queryBuilder->setParameter($event->getParameterName(), $event->getParameterValue());
    }

    private function dispatch(string $eventName, DoctrineOrmFilterEvent $event): void
    {
        $dispatcher = $event->getFilter()->getConfig()->getEventDispatcher();

        if ($dispatcher->hasListeners($eventName)) {
            $dispatcher->dispatch($event, $eventName);
        }
    }

    private function createExpression(FilterData $data, string $queryPath, string $parameterName): mixed
    {
        $comparison = ($this->comparisonFactory)($data, new Expr());

        return $comparison($queryPath, ":$parameterName");
    }

    private function getParameterValue(FilterData $data): mixed
    {
        return ($this->parameterValueTransformer)($data);
    }
}
