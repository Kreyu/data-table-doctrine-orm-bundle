<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Tests\Filter;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Kreyu\Bundle\DataTableBundle\Exception\UnexpectedTypeException;
use Kreyu\Bundle\DataTableBundle\Filter\FilterConfigInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableBundle\Filter\Operator;
use Kreyu\Bundle\DataTableBundle\Query\ProxyQueryInterface;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\DoctrineOrmFilterHandler;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\LowerExpressionTransformer;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\TrimExpressionTransformer;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\UpperExpressionTransformer;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Query\DoctrineOrmProxyQueryInterface;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Tests\Fixtures\Filter\ExpressionTransformer\CustomExpressionTransformer;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Tests\Fixtures\Query\NotSupportedProxyQuery;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DoctrineOrmFilterHandlerTest extends TestCase
{
    public function testHandlingWithNotSupportedProxyQueryClass(): void
    {
        $query = new NotSupportedProxyQuery();

        $this->expectExceptionObject(new UnexpectedTypeException($query, DoctrineOrmProxyQueryInterface::class));

        $this->handle($query);
    }

    private function createHandler(): DoctrineOrmFilterHandler
    {
        return new DoctrineOrmFilterHandler(
            fn () => (new Expr())->eq(...),
            fn () => 'value',
        );
    }

    private function handle(ProxyQueryInterface $query = null, FilterData $data = null, FilterInterface $filter = null): void
    {
        $this->createHandler()->handle(
            $query ?? $this->createDoctrineOrmProxyQueryMock(),
            $data ?? $this->createFilterDataMock(),
            $filter ?? $this->createFilterMock(),
        );
    }

    private function createDoctrineOrmProxyQueryMock(): DoctrineOrmProxyQueryInterface&MockObject
    {
        return $this->createMock(DoctrineOrmProxyQueryInterface::class);
    }

    private function createQueryBuilderMock(): QueryBuilder&MockObject
    {
        return $this->createMock(QueryBuilder::class);
    }

    private function createFilterDataMock(): FilterData&MockObject
    {
        return $this->createMock(FilterData::class);
    }

    private function createFilterMock(array $options = []): FilterInterface&MockObject
    {
        $filterConfig = $this->createMock(FilterConfigInterface::class);
        $filterConfig->method('getOption')->willReturnCallback(function (string $option) use ($options) {
            return $options[$option] ?? null;
        });

        $filter = $this->createMock(FilterInterface::class);
        $filter->method('getConfig')->willReturn($filterConfig);

        return $filter;
    }

    private function createFilterConfigMock(): FilterConfigInterface&MockObject
    {
        return $this->createMock(FilterConfigInterface::class);
    }
}
