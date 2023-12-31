<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionFactory;

use Doctrine\ORM\Query\Parameter;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Query\DoctrineOrmProxyQueryInterface;

interface ExpressionFactoryInterface
{
    /**
     * @param array<Parameter> $parameters
     */
    public function create(DoctrineOrmProxyQueryInterface $query, FilterData $data, FilterInterface $filter, array $parameters): mixed;
}
