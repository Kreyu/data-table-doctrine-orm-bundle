<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Tests\Filter\Type;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\Operator;
use Kreyu\Bundle\DataTableBundle\Test\Filter\FilterTypeTestCase;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\DoctrineOrmFilterHandler;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Query\DoctrineOrmProxyQuery;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Query\DoctrineOrmProxyQueryInterface;
use PHPUnit\Framework\Attributes\DataProvider;

abstract class DoctrineOrmFilterTypeTestCase extends FilterTypeTestCase
{
    abstract public static function operatorExpressionProvider(): iterable;

    #[DataProvider('operatorExpressionProvider')]
    public function testApplyingFilter(Operator $operator, callable $expression): void
    {
        $query = $this->createMock(DoctrineOrmProxyQueryInterface::class);

        $handler = $this->createMock(DoctrineOrmFilterHandler::class);
        $handler->expects($this->once())->method('handle')->with();

        $filterBuilder = $this->factory->createBuilder();
        $filterBuilder->setHandler($handler);

        $filter = $filterBuilder->getFilter();
        $filter->handle($query, new FilterData(operator: $operator));
    }
}
