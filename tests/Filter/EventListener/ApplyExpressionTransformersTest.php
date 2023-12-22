<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Tests\Filter\EventListener;

use Doctrine\ORM\Query\Expr;
use Kreyu\Bundle\DataTableBundle\Filter\FilterConfigInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Event\PreApplyExpressionEvent;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\EventListener\ApplyExpressionTransformers;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\ExpressionTransformerInterface;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\LowerExpressionTransformer;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\TrimExpressionTransformer;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\UpperExpressionTransformer;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Query\DoctrineOrmProxyQueryInterface;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Tests\Fixtures\Filter\ExpressionTransformer\CustomExpressionTransformer;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ApplyExpressionTransformersTest extends TestCase
{
    protected function setUp(): void
    {
        $this->listener = new ApplyExpressionTransformers();
    }

    public static function builtInExpressionTransformersOptionsProvider(): \Generator
    {
        yield 'trim' => ['option' => 'trim', 'transformer' => new TrimExpressionTransformer()];
        yield 'lower' => ['option' => 'lower', 'transformer' => new LowerExpressionTransformer()];
        yield 'upper' => ['option' => 'upper', 'transformer' => new UpperExpressionTransformer()];
    }

    #[DataProvider('builtInExpressionTransformersOptionsProvider')]
    public function testBuiltInExpressionTransformerOptions(string $option, ExpressionTransformerInterface $expressionTransformer): void
    {
        $filter = $this->createFilterMock([
            $option => true,
            'expression_transformers' => [],
        ]);

        $query = $this->createDoctrineOrmProxyQueryMock();

        $data = $this->createFilterDataMock();

        $expression = new Expr\Comparison('foo', '=', 'bar');

        $event = new PreApplyExpressionEvent($filter, $query, $data, $expression);

        $this->listener->preApplyExpression($event);

        $this->assertEquals($expressionTransformer->transform($expression), $event->getExpression());
    }

    public function testBuiltInExpressionTransformersOptionsChainWithCustomOnes(): void
    {
        $filter = $this->createFilterMock([
            'trim' => true,
            'lower' => true,
            'upper' => true,
            'expression_transformers' => [
                new CustomExpressionTransformer(),
            ],
        ]);

        $query = $this->createDoctrineOrmProxyQueryMock();

        $data = $this->createFilterDataMock();

        $expression = new Expr\Comparison('foo', '=', 'bar');

        $event = new PreApplyExpressionEvent($filter, $query, $data, $expression);

        $this->listener->preApplyExpression($event);

        $expectedExpression = $expression;
        $expectedExpression = (new TrimExpressionTransformer())->transform($expectedExpression);
        $expectedExpression = (new LowerExpressionTransformer())->transform($expectedExpression);
        $expectedExpression = (new UpperExpressionTransformer())->transform($expectedExpression);
        $expectedExpression = (new CustomExpressionTransformer())->transform($expectedExpression);

        $this->assertEquals($expectedExpression, $event->getExpression());
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

    private function createFilterDataMock(): FilterData&MockObject
    {
        return $this->createMock(FilterData::class);
    }

    private function createDoctrineOrmProxyQueryMock(): DoctrineOrmProxyQueryInterface&MockObject
    {
        return $this->createMock(DoctrineOrmProxyQueryInterface::class);
    }
}