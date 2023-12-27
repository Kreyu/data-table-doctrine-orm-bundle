<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type;

use Kreyu\Bundle\DataTableBundle\Filter\Type\AbstractFilterType;

abstract class AbstractDoctrineOrmFilterType extends AbstractFilterType
{
    public function getParent(): ?string
    {
        return DoctrineOrmFilterType::class;
    }
}
