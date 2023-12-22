<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Query;

use Doctrine\ORM\QueryBuilder;
use Kreyu\Bundle\DataTableBundle\Query\ProxyQueryInterface;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Paginator\PaginatorFactoryInterface;

/**
 * @mixin QueryBuilder
 */
interface DoctrineOrmProxyQueryInterface extends ProxyQueryInterface
{
    public function getQueryBuilder(): QueryBuilder;

    public function getUniqueParameterId(): int;

    /**
     * @return array<string, mixed>
     */
    public function getHints(): array;

    public function setHint(string $name, mixed $value): void;

    /**
     * @psalm-return string|AbstractQuery::HYDRATE_*
     */
    public function getHydrationMode(): string;

    /**
     * @psalm-param string|AbstractQuery::HYDRATE_* $hydrationMode
     */
    public function setHydrationMode(int|string $hydrationMode): void;

    public function getBatchSize(): int;

    public function setBatchSize(int $batchSize): void;

    public function getPaginatorFactory(): PaginatorFactoryInterface;

    public function setPaginatorFactory(PaginatorFactoryInterface $paginatorFactory): void;

    public function getAliasResolver(): AliasResolverInterface;

    public function setAliasResolver(AliasResolverInterface $aliasResolver): void;
}
