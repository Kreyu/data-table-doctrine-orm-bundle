<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Tests\Filter\Handler;

use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableBundle\Filter\Operator;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionFactory\ExpressionFactoryInterface;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Handler\DateRangeFilterHandler;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ParameterFactory\ParameterFactoryInterface;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Query\DoctrineOrmProxyQueryInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class DateRangeFilterHandlerTest extends TestCase
{
    private MockObject&ParameterFactoryInterface $parameterFactory;
    private DateRangeFilterHandler $handler;

    protected function setUp(): void
    {
        $this->handler = new DateRangeFilterHandler(
            $this->createMock(ExpressionFactoryInterface::class),
            $this->parameterFactory = $this->createMock(ParameterFactoryInterface::class),
        );
    }

    public static function filterDataTransformationProvider(): iterable
    {
        yield 'empty value' => [
            'input' => new FilterData(),
            'output' => new FilterData(),
        ];

        yield 'value from only' => [
            'input' => new FilterData(
                value: ['from' => new \DateTime('2022-01-01 11:22:33')],
                operator: Operator::Between,
            ),
            'output' => new FilterData(
                value: new \DateTime('2022-01-01 00:00:00'),
                operator: Operator::GreaterThanEquals,
            ),
        ];

        yield 'value to only' => [
            'input' => new FilterData(
                value: ['to' => new \DateTime('2022-01-01 11:22:33')],
                operator: Operator::Between,
            ),
            'output' => new FilterData(
                value: new \DateTime('2022-01-02 00:00:00'),
                operator: Operator::LessThan,
            ),
        ];

        yield 'value from and to' => [
            'input' => new FilterData(
                value: [
                    'from' => new \DateTime('2022-01-01 11:22:33'),
                    'to' => new \DateTime('2022-01-02 11:22:33'),
                ],
                operator: Operator::Between,
            ),
            'output' => new FilterData(
                value: [
                    'from' => new \DateTime('2022-01-01 00:00:00'),
                    'to' => new \DateTime('2022-01-03 00:00:00'),
                ],
                operator: Operator::Between,
            ),
        ];
    }

    #[DataProvider('filterDataTransformationProvider')]
    public function testItTransformsFilterData(FilterData $input, FilterData $output): void
    {
        $this->parameterFactory
            ->expects($this->once())
            ->method('create')
            ->willReturnCallback(function (DoctrineOrmProxyQueryInterface $query, FilterData $data) use ($output) {
                $this->assertEquals($output, $data);

                return [];
            });

        $this->handle($input);
    }

    private function handle(FilterData $data): void
    {
        $query = $this->createMock(DoctrineOrmProxyQueryInterface::class);
        $filter = $this->createMock(FilterInterface::class);

        $this->handler->handle($query, $data, $filter);
    }
}
