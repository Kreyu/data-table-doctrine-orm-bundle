<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ParameterFactory;

use Doctrine\ORM\Query\Parameter;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Query\DoctrineOrmProxyQueryInterface;

interface ParameterFactoryInterface
{
    /**
     * @return array<Parameter>
     */
    public function create(DoctrineOrmProxyQueryInterface $query, FilterData $data, FilterInterface $filter): array;
}
