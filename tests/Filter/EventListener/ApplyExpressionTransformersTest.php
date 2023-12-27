<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Tests\Filter\EventListener;

use Doctrine\ORM\Query\Expr;
use Kreyu\Bundle\DataTableBundle\Filter\FilterConfigInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Event\PreApplyExpressionEvent;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\EventListener\ApplyExpressionTransformers;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\LowerExpressionTransformer;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\TrimExpressionTransformer;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\UpperExpressionTransformer;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Query\DoctrineOrmProxyQueryInterface;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Tests\Fixtures\Filter\ExpressionTransformer\CustomExpressionTransformer;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class ApplyExpressionTransformersTest extends TestCase
{
    private ApplyExpressionTransformers $listener;

    protected function setUp(): void
    {
        $this->listener = new ApplyExpressionTransformers();
    }

    public function testItChainsBuiltInTransformersWithCustomOnes(): void
    {
        $filter = $this->createFilterMock([
            'trim' => true,
            'lower' => true,
            'upper' => true,
            'expression_transformers' => [
                new CustomExpressionTransformer(),
            ],
        ]);

        $expression = $expectedExpression = new Expr\Comparison('foo', '=', 'bar');

        $event = new PreApplyExpressionEvent(
            $filter,
            $this->createFilterDataMock(),
            $this->createDoctrineOrmProxyQueryMock(),
            $expression,
        );

        $this->listener->preApplyExpression($event);

        $expressionTransformers = [
            new TrimExpressionTransformer(),
            new LowerExpressionTransformer(),
            new UpperExpressionTransformer(),
            new CustomExpressionTransformer(),
        ];

        foreach ($expressionTransformers as $expressionTransformer) {
            $expectedExpression = $expressionTransformer->transform($expectedExpression);
        }

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
