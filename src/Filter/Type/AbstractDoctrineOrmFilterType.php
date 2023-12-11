<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type;

use Doctrine\ORM\Query\Expr;
use Kreyu\Bundle\DataTableBundle\Filter\FilterBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\Type\AbstractFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\DoctrineOrmFilterHandler;

abstract class AbstractDoctrineOrmFilterType extends AbstractFilterType
{
    public function buildFilter(FilterBuilderInterface $builder, array $options): void
    {
        $builder->setHandler(new DoctrineOrmFilterHandler(
            $this->createComparison(...),
            $this->getParameterValue(...),
        ));
    }

    public function getParent(): ?string
    {
        return DoctrineOrmFilterType::class;
    }

    protected function createComparison(FilterData $data, Expr $expr): mixed
    {
        return $expr->eq(...);
    }

    protected function getParameterValue(FilterData $data): mixed
    {
        return $data->getValue();
    }
}
