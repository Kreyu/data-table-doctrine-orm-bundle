<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Event;

use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableBundle\Query\ProxyQueryInterface;
use Symfony\Contracts\EventDispatcher\Event;

class DoctrineOrmFilterEvent extends Event
{
    public function __construct(
        private readonly FilterInterface $filter,
        private readonly ProxyQueryInterface $query,
        private readonly FilterData $data,
    ) {
    }

    public function getFilter(): FilterInterface
    {
        return $this->filter;
    }

    public function getQuery(): ProxyQueryInterface
    {
        return $this->query;
    }

    public function getData(): FilterData
    {
        return $this->data;
    }
}
