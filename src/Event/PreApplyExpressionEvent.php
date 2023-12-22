<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Event;

use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableBundle\Query\ProxyQueryInterface;

class PreApplyExpressionEvent extends DoctrineOrmFilterEvent
{
    public function __construct(
        FilterInterface $filter,
        ProxyQueryInterface $query,
        FilterData $data,
        private mixed $expression,
    ) {
        parent::__construct($filter, $query, $data);
    }

    public function getExpression(): mixed
    {
        return $this->expression;
    }

    public function setExpression(mixed $expression): void
    {
        $this->expression = $expression;
    }
}
