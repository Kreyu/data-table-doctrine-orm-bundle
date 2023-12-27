<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter;

use Kreyu\Bundle\DataTableBundle\Exception\UnexpectedTypeException;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterHandlerInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableBundle\Query\ProxyQueryInterface;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Event\DoctrineOrmFilterEvent;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Event\DoctrineOrmFilterEvents;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Event\PreApplyExpressionEvent;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Event\PreSetParametersEvent;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionFactory\ExpressionFactory;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionFactory\ExpressionFactoryInterface;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ParameterFactory\ParameterFactory;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ParameterFactory\ParameterFactoryInterface;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Query\DoctrineOrmProxyQueryInterface;

class DoctrineOrmFilterHandler implements FilterHandlerInterface
{
    public function __construct(
        private readonly ExpressionFactoryInterface $expressionFactory = new ExpressionFactory(),
        private readonly ParameterFactoryInterface $parameterFactory = new ParameterFactory(),
    ) {
    }

    public function handle(ProxyQueryInterface $query, FilterData $data, FilterInterface $filter): void
    {
        if (!$query instanceof DoctrineOrmProxyQueryInterface) {
            throw new UnexpectedTypeException($query, DoctrineOrmProxyQueryInterface::class);
        }

        $parameters = $this->parameterFactory->createParameters($filter, $data, $query);

        $event = new PreSetParametersEvent($filter, $data, $query, $parameters);

        $this->dispatch(DoctrineOrmFilterEvents::PRE_SET_PARAMETERS, $event);

        $queryBuilder = $query->getQueryBuilder();

        foreach ($event->getParameters() as $parameter) {
            $queryBuilder->setParameter($parameter->getName(), $parameter->getValue(), $parameter->getType());
        }

        $expression = $this->expressionFactory->createExpression($filter, $data, $query, $event->getParameters());

        $event = new PreApplyExpressionEvent($filter, $data, $query, $expression);

        $this->dispatch(DoctrineOrmFilterEvents::PRE_APPLY_EXPRESSION, $event);

        $queryBuilder->andWhere($event->getExpression());
    }

    private function dispatch(string $eventName, DoctrineOrmFilterEvent $event): void
    {
        $dispatcher = $event->getFilter()->getConfig()->getEventDispatcher();

        if ($dispatcher->hasListeners($eventName)) {
            $dispatcher->dispatch($event, $eventName);
        }
    }
}
