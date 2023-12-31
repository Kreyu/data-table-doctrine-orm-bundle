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
    private DoctrineOrmProxyQueryFactory $factory;

    protected function setUp(): void
    {
        $this->factory = new DoctrineOrmProxyQueryFactory();
    }

    public function testCreatingWithSupportedData(): void
    {
        $queryBuilder = $this->createStub(QueryBuilder::class);

        $data = $this->factory->create($queryBuilder);

        $this->assertInstanceOf(DoctrineOrmProxyQuery::class, $data);
        $this->assertEquals($queryBuilder, $data->getQueryBuilder());
    }

    public function testCreatingWithNotSupportedData(): void
    {
        $data = $this->createStub(Query::class);

        $this->expectExceptionObject(new UnexpectedTypeException($data, QueryBuilder::class));

        $this->factory->create($data);
    }
}
