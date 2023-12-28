<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Query;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Kreyu\Bundle\DataTableBundle\Exception\InvalidArgumentException;
use Kreyu\Bundle\DataTableBundle\Pagination\PaginationData;
use Kreyu\Bundle\DataTableBundle\Query\ResultSetInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Paginator\PaginatorFactory;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Paginator\PaginatorFactoryInterface;

/**
 * @mixin QueryBuilder
 */
class DoctrineOrmProxyQuery implements DoctrineOrmProxyQueryInterface
{
    private int $uniqueParameterId = 0;
    private int $batchSize = 5000;
    private PaginatorFactoryInterface $paginatorFactory;
    private AliasResolverInterface $aliasResolver;

    /**
     * @param array<string, mixed> $hints
     */
    public function __construct(
        private QueryBuilder $queryBuilder,
        private array $hints = [],
        private string|int $hydrationMode = AbstractQuery::HYDRATE_OBJECT,
    ) {
    }

    public function __call(string $name, array $args): mixed
    {
        return $this->queryBuilder->$name(...$args);
    }

    public function __get(string $name): mixed
    {
        return $this->queryBuilder->{$name};
    }

    public function __clone(): void
    {
        $this->queryBuilder = clone $this->queryBuilder;
    }

    public function sort(SortingData $sortingData): void
    {
        if (empty($sortCriteria = $sortingData->getColumns())) {
            return;
        }

        $orderByDQLPart = $this->queryBuilder->getDQLPart('orderBy');

        $this->queryBuilder->resetDQLPart('orderBy');

        foreach ($sortCriteria as $sortCriterion) {
            $sortCriterionPath = $this->getAliasResolver()->resolve((string) $sortCriterion->getPropertyPath(), $this->queryBuilder);

            $this->queryBuilder->addOrderBy($sortCriterionPath, $sortCriterion->getDirection());

            foreach ($orderByDQLPart as $orderBy) {
                $this->queryBuilder->addOrderBy($orderBy);
            }
        }
    }

    public function paginate(PaginationData $paginationData): void
    {
        $this->queryBuilder
            ->setFirstResult($paginationData->getOffset())
            ->setMaxResults($paginationData->getPerPage())
        ;
    }

    public function getResult(): ResultSetInterface
    {
        return new DoctrineOrmResultSet($this->getPaginatorFactory()->create($this->queryBuilder, $this->hints));
    }

    public function getQueryBuilder(): QueryBuilder
    {
        return $this->queryBuilder;
    }

    public function getUniqueParameterId(): int
    {
        return $this->uniqueParameterId++;
    }

    public function getHints(): array
    {
        return $this->hints;
    }

    public function setHint(string $name, mixed $value): void
    {
        $this->hints[$name] = $value;
    }

    public function getHydrationMode(): int|string
    {
        return $this->hydrationMode;
    }

    public function setHydrationMode(int|string $hydrationMode): void
    {
        $this->hydrationMode = $hydrationMode;
    }

    public function getBatchSize(): int
    {
        return $this->batchSize;
    }

    public function setBatchSize(int $batchSize): void
    {
        if ($batchSize <= 0) {
            throw new InvalidArgumentException('The batch size must be positive');
        }

        $this->batchSize = $batchSize;
    }

    public function getPaginatorFactory(): PaginatorFactoryInterface
    {
        return $this->paginatorFactory ??= new PaginatorFactory();
    }

    public function setPaginatorFactory(PaginatorFactoryInterface $paginatorFactory): void
    {
        $this->paginatorFactory = $paginatorFactory;
    }

    public function getAliasResolver(): AliasResolverInterface
    {
        return $this->aliasResolver ??= new AliasResolver();
    }

    public function setAliasResolver(AliasResolverInterface $aliasResolver): void
    {
        $this->aliasResolver = $aliasResolver;
    }
}
