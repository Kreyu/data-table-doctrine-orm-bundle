<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Query;

use Doctrine\ORM\QueryBuilder;

interface AliasResolverInterface
{
    public function resolve(string $queryPath, QueryBuilder $queryBuilder): string;
}
