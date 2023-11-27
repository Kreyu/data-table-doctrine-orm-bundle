<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Tests\Filter\Type;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\Operator;
use Kreyu\Bundle\DataTableBundle\Test\Filter\FilterTypeTestCase;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Query\DoctrineOrmProxyQuery;
use PHPUnit\Framework\Attributes\DataProvider;

abstract class DoctrineOrmFilterTypeTestCase extends FilterTypeTestCase
{
    abstract public static function operatorExpressionProvider(): iterable;

    #[DataProvider('operatorExpressionProvider')]
    public function testApplyingFilter(Operator $operator, callable $expression, mixed $value = ''): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('getRootAliases')->willReturn([]);
        $queryBuilder->method('expr')->willReturn(new Expr());

        $queryBuilder->expects($this->once())
            ->method('andWhere')
            ->with($expression('foo', ':test_0'))
            ->willReturn($queryBuilder)
        ;

        $query = $this->createMock(DoctrineOrmProxyQuery::class);
        $query->method('getQueryBuilder')->willReturn($queryBuilder);

        $filter = $this->createNamedFilter('test', ['query_path' => 'foo']);
        $filter->apply($query, new FilterData($value, $operator));
    }
}
