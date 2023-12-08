<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Query;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Kreyu\Bundle\DataTableBundle\Query\ResultSet;
use Kreyu\Bundle\DataTableBundle\Util\RewindableGeneratorIterator;

class DoctrineOrmResultSet extends ResultSet
{
    public function __construct(Paginator $paginator, int $batchSize = 5000)
    {
        $items = new RewindableGeneratorIterator(fn () => $this->getPaginatorItems($paginator, $batchSize));

        $itemCount = $totalItemCount = $paginator->count();

        if (null !== $paginator->getQuery()->getMaxResults()) {
            $items = new \ArrayIterator(iterator_to_array($items));
            $itemCount = iterator_count($items);
        }

        parent::__construct($items, $itemCount, $totalItemCount);
    }

    private function getPaginatorItems(Paginator $paginator, int $batchSize): \Generator
    {
        $query = $paginator->getQuery();

        $firstResult = $query->getFirstResult();
        $maxResults = $limit = $query->getMaxResults();

        if (null === $maxResults || $maxResults > $batchSize) {
            $maxResults = $batchSize;
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

            $paginator->getQuery()->getEntityManager()->clear();
        }
    }
}
