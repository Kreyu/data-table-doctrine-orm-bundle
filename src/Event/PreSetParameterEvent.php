<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Event;

use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableBundle\Query\ProxyQueryInterface;

class PreSetParameterEvent extends DoctrineOrmFilterEvent
{
    public function __construct(
        FilterInterface $filter,
        ProxyQueryInterface $query,
        FilterData $data,
        private string $parameterName,
        private mixed $parameterValue,
    ) {
        parent::__construct($filter, $query, $data);
    }

    public function getParameterName(): string
    {
        return $this->parameterName;
    }

    public function setParameterName(string $parameterName): void
    {
        $this->parameterName = $parameterName;
    }

    public function getParameterValue(): mixed
    {
        return $this->parameterValue;
    }

    public function setParameterValue(mixed $parameterValue): void
    {
        $this->parameterValue = $parameterValue;
    }
}
