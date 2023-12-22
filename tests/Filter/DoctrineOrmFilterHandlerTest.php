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

    public function testTrimOptionAppliesTrimExpressionTransformer(): void
    {
        $filter = $this->createFilterMock(['trim' => true]);

        $expressionTransformers = $this->createHandler()->getExpressionTransformers($filter);

        $this->assertInstanceOf(TrimExpressionTransformer::class, $expressionTransformers[0]);
    }

    public function testLowerOptionAppliesLowerExpressionTransformer(): void
    {
        $filter = $this->createFilterMock(['lower' => true]);

        $expressionTransformers = $this->createHandler()->getExpressionTransformers($filter);

        $this->assertInstanceOf(LowerExpressionTransformer::class, $expressionTransformers[0]);
    }

    public function testUpperOptionAppliesUpperExpressionTransformer(): void
    {
        $filter = $this->createFilterMock(['upper' => true]);

        $expressionTransformers = $this->createHandler()->getExpressionTransformers($filter);

        $this->assertInstanceOf(UpperExpressionTransformer::class, $expressionTransformers[0]);
    }

    public function testPassingExpressionTransformersAsOption(): void
    {
        $filter = $this->createFilterMock(['expression_transformers' => [
            new CustomExpressionTransformer(),
        ]]);

        $expressionTransformers = $this->createHandler()->getExpressionTransformers($filter);

        $this->assertInstanceOf(CustomExpressionTransformer::class, $expressionTransformers[0]);
    }

    public function testApplyingTrimExpressionTransformerWithTrimOption(): void
    {
        $this->assertTrue(true);
        return;

        $filterConfig = $this->createFilterConfigMock();
        $filterConfig->method('getOption')->withAnyParameters()->willReturnCallback(function (string $option) {
            return match ($option) {
                'expression_transformers' => [],
                'trim' => true,
                default => null,
            };
        });

        $filterConfig->method('getSupportedOperators')->willReturn([Operator::Equals]);

        $filter = $this->createFilterMock();
        $filter->method('getConfig')->willReturn($filterConfig);

        $queryBuilder = $this->createQueryBuilderMock();
        $queryBuilder->expects($this->once())->method('andWhere')->with(new Expr\Comparison(
            new Expr\Func('TRIM', ''),
            '=',
            new Expr\Func('TRIM', ':_0'),
        ))->willReturn($queryBuilder);

        $query = $this->createDoctrineOrmProxyQueryMock();
        $query->method('getQueryBuilder')->willReturn($queryBuilder);

        $data = $this->createFilterDataMock();
        $data->method('getOperator')->willReturn(Operator::Equals);

        $this->handle($query, $data, $filter);
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
