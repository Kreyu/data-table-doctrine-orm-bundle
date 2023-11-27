<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Tests\Query;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Kreyu\Bundle\DataTableBundle\Exception\UnexpectedTypeException;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Query\DoctrineOrmProxyQuery;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Query\DoctrineOrmProxyQueryFactory;
use PHPUnit\Framework\TestCase;

class DoctrineOrmProxyQueryFactoryTest extends TestCase
{
    public function testCreatingWithSupportedData(): void
    {
        $factory = new DoctrineOrmProxyQueryFactory();

        $queryBuilder = $this->createMock(QueryBuilder::class);

        $query = $factory->create($queryBuilder);

        $this->assertInstanceOf(DoctrineOrmProxyQuery::class, $query);
        $this->assertEquals($queryBuilder, $query->getQueryBuilder());
    }

    public function testCreatingWithNotSupportedData(): void
    {
        $factory = new DoctrineOrmProxyQueryFactory();

        $data = $this->createMock(Query::class);

        $this->expectExceptionObject(new UnexpectedTypeException($data, QueryBuilder::class));

        $factory->create($data);
    }
}
