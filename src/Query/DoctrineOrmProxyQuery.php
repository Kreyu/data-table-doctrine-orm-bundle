<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Query;

use Doctrine\ORM\AbstractQuery;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\CountWalker;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Kreyu\Bundle\DataTableBundle\Exception\InvalidArgumentException;
use Kreyu\Bundle\DataTableBundle\Pagination\PaginationData;
use Kreyu\Bundle\DataTableBundle\Query\PaginationAwareResultSet;
use Kreyu\Bundle\DataTableBundle\Query\PaginationAwareResultSetInterface;
use Kreyu\Bundle\DataTableBundle\Query\ResultSet;
use Kreyu\Bundle\DataTableBundle\Query\ResultSetInterface;
use Kreyu\Bundle\DataTableBundle\Sorting\SortingData;
use Kreyu\Bundle\DataTableBundle\Util\RewindableGeneratorIterator;

/**
 * Wrapper around the query builder ({@see QueryBuilder}) with additional features and fixes.
 * The underlying query builder methods can be accessed directly, for example:
 *
 * ```
 * $proxyQuery = new DoctrineOrmProxyQuery($queryBuilder);
 * $proxyQuery->andWhere(...);
 * $proxyQuery->setParameter(...);
 * ```
 *
 * The ProxyQuery class concept comes from the SonataDoctrineORMAdminBundle,
 * and a part of this class code is copied from their repository.
 *
 * @see https://github.com/sonata-project/SonataDoctrineORMAdminBundle
 *
 * @mixin QueryBuilder
 */
class DoctrineOrmProxyQuery implements DoctrineOrmProxyQueryInterface
{
    private int $uniqueParameterId = 0;
    private int $batchSize = 5000;
    private ?PaginationData $paginationData = null;
    private ?SortingData $sortingData = null;

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

    /**
     * Retrieves an instance of the underlying query builder, with additional sort criteria applied.
     * Each sort criterion path gets its alias resolved (e.g. "name" will result in "product.name").
     *
     * Note: the sort criteria is applied **before** the order by DQL part already present in the query builder.
     */
    public function getQueryBuilder(): QueryBuilder
    {
        $queryBuilder = clone $this->queryBuilder;

        $rootAlias = current($queryBuilder->getRootAliases());

        $sortCriteria = $this->sortingData?->getColumns();

        if (!empty($sortCriteria)) {
            $orderByDQLPart = $queryBuilder->getDQLPart('orderBy');
            $queryBuilder->resetDQLPart('orderBy');

            foreach ($sortCriteria as $sortCriterion) {
                $sortCriterionPath = (string) $sortCriterion->getPropertyPath();

                $isRootAliasResolvable = !str_contains($sortCriterionPath, '.')
                    && !str_contains($sortCriterionPath, '(')
                    && !str_starts_with($sortCriterionPath, '__');

                if ($isRootAliasResolvable) {
                    $sortCriterionPath = $rootAlias.'.'.$sortCriterionPath;
                }

                $queryBuilder->addOrderBy($sortCriterionPath, $sortCriterion->getDirection());

                foreach ($orderByDQLPart as $orderBy) {
                    $queryBuilder->addOrderBy($orderBy);
                }
            }
        }

        return $queryBuilder;
    }

    public function sort(SortingData $sortingData): void
    {
        $this->sortingData = $sortingData;
    }

    public function paginate(PaginationData $paginationData): void
    {
        $this->paginationData = $paginationData;
    }

    public function getResult(): ResultSetInterface|PaginationAwareResultSetInterface
    {
        $queryBuilder = $this->getQueryBuilder();

        // The Doctrine Paginator is utilized in both paginated and non-paginated result set.
        // This helps with retrieving the items in batches, clearing the entity manager in between.
        $paginator = $this->createPaginator($queryBuilder);

        // Using the generator alone allows iterating (and counting, since it moves the cursor) only once.
        // Thanks to this wrapper, the iterator recreates the generator on rewind by using the given callable.
        $items = new RewindableGeneratorIterator(fn () => $this->getPaginatorItems($paginator));

        // Retrieve total item count from the Doctrine Paginator, because it executes a distinct count query.
        // Counting the items on the PHP side would load whole data set into the memory.
        $itemCount = $paginator->count();

        if (null !== $this->paginationData) {
            return new PaginationAwareResultSet(iterator_to_array($items), $itemCount);
        }

        return new ResultSet($items, $itemCount);
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

    public function getHydrationMode(): string
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
        if ($batchSize < 0) {
            throw new InvalidArgumentException('The batch size cannot be negative');
        }

        $this->batchSize = $batchSize;
    }

    /**
     * Retrieves items of given paginator in the form of the generator.
     * The items are yielded in batches, clearing the entity manager in between.
     *
     * Note: the batch size can be configured using the {@see DoctrineOrmProxyQueryInterface::setBatchSize()} method.
     */
    private function getPaginatorItems(Paginator $paginator): \Generator
    {
        $query = $paginator->getQuery();

        $firstResult = $this->paginationData?->getOffset() ?? 0;
        $maxResults = $limit = $this->paginationData?->getPerPage();

        if (null === $maxResults || $maxResults > $this->batchSize) {
            $maxResults = $this->batchSize;
        }

        $hasItems = true;

        $cursorPosition = 0;

        while ($hasItems && $firstResult < $paginator->count() && (null === $limit || $cursorPosition < $limit)) {
            $hasItems = false;

            $query
                ->setMaxResults($maxResults)
                ->setFirstResult($firstResult);

            foreach ($paginator as $item) {
                yield $item;

                $hasItems = true;

                ++$cursorPosition;
            }

            $firstResult += $cursorPosition;

            $this->getEntityManager()->clear();
        }
    }

    /**
     * Creates a paginator from the given query builder, with multiple workarounds and fixes:
     *
     * - the distinct count walker hint ({@see CountWalker::HINT_DISTINCT}) is set to `false` when query has no joins
     * - the fetch join collection paginator is created only when query has single primary key and joins
     * - the output walkers are disabled for simple queries ({@see DoctrineOrmProxyQuery::canDisableOutPutWalkers()})
     */
    private function createPaginator(QueryBuilder $queryBuilder): Paginator
    {
        $rootEntity = current($queryBuilder->getRootEntities());

        if (false === $rootEntity) {
            throw new \RuntimeException('There are not root entities defined in the query.');
        }

        $identifierFieldNames = $queryBuilder
            ->getEntityManager()
            ->getClassMetadata($rootEntity)
            ->getIdentifierFieldNames();

        $hasSingleIdentifierName = 1 === \count($identifierFieldNames);
        $hasJoins = \count($queryBuilder->getDQLPart('join')) > 0;

        $query = $queryBuilder->getQuery();

        if (!$hasJoins) {
            $query->setHint(CountWalker::HINT_DISTINCT, false);
        }

        foreach ($this->hints as $name => $value) {
            $query->setHint($name, $value);
        }

        // Paginator with fetchJoinCollection doesn't work with composite primary keys
        // https://github.com/doctrine/orm/issues/2910
        // To stay safe fetch join only when we have single primary key and joins
        $paginator = new Paginator($query, $hasSingleIdentifierName && $hasJoins);

        // it is only safe to disable output walkers for really simple queries
        if ($this->canDisableOutPutWalkers($queryBuilder)) {
            $paginator->setUseOutputWalkers(false);
        }

        return $paginator;
    }

    /**
     * @see https://github.com/doctrine/orm/issues/8278#issue-705517756
     */
    private function canDisableOutPutWalkers(QueryBuilder $queryBuilder): bool
    {
        // Do not support queries using HAVING
        if (null !== $queryBuilder->getDQLPart('having')) {
            return false;
        }

        $fromParts = $queryBuilder->getDQLPart('from');

        // Do not support queries using multiple entities in FROM
        if (1 !== \count($fromParts)) {
            return false;
        }

        $fromPart = current($fromParts);

        $classMetadata = $queryBuilder
            ->getEntityManager()
            ->getClassMetadata($fromPart->getFrom());

        $identifierFieldNames = $classMetadata->getIdentifierFieldNames();

        // Do not support entities using a composite identifier
        if (1 !== \count($identifierFieldNames)) {
            return false;
        }

        $identifierName = current($identifierFieldNames);

        // Do not support entities using a foreign key as identifier
        if ($classMetadata->hasAssociation($identifierName)) {
            return false;
        }

        // Do not support queries using a field from a toMany relation in the ORDER BY clause
        if ($this->hasOrderByWithToManyAssociation($queryBuilder)) {
            return false;
        }

        return true;
    }

    private function hasOrderByWithToManyAssociation(QueryBuilder $queryBuilder): bool
    {
        $joinParts = $queryBuilder->getDQLPart('join');

        if (0 === \count($joinParts)) {
            return false;
        }

        $sortCriteria = $this->sortingData?->getColumns() ?? [];
        $orderByParts = $queryBuilder->getDQLPart('orderBy');

        if (empty($sortCriteria) && empty($orderByParts)) {
            return false;
        }

        $joinAliases = [];

        foreach ($joinParts as $joinPart) {
            foreach ($joinPart as $join) {
                $joinAliases[] = $join->getAlias();
            }
        }

        foreach ($sortCriteria as $sortCriterion) {
            foreach ($joinAliases as $joinAlias) {
                if (str_starts_with((string) $sortCriterion->getPropertyPath(), $joinAlias.'.')) {
                    return true;
                }
            }
        }

        foreach ($orderByParts as $orderByPart) {
            foreach ($orderByPart->getParts() as $part) {
                foreach ($joinAliases as $joinAlias) {
                    if (str_starts_with($part, $joinAlias.'.')) {
                        return true;
                    }
                }
            }
        }

        return false;
    }
}
