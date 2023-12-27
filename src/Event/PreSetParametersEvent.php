<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Event;

use Doctrine\ORM\Query\Parameter;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableBundle\Query\ProxyQueryInterface;

class PreSetParametersEvent extends DoctrineOrmFilterEvent
{
    public function __construct(
        FilterInterface $filter,
        FilterData $data,
        ProxyQueryInterface $query,
        private array $parameters,
    ) {
        parent::__construct($filter, $data, $query);
    }

    /**
     * @return array<Parameter>
     */
    public function getParameters(): array
    {
        return $this->parameters;
    }

    public function setParameters(array $parameters): void
    {
        $this->parameters = $parameters;
    }
}
