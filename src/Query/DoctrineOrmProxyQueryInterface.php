<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Query;

use Doctrine\ORM\QueryBuilder;
use Kreyu\Bundle\DataTableBundle\Query\ProxyQueryInterface;

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
}
