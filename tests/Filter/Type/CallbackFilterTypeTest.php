<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Tests\Filter\Type;

use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Test\Filter\FilterTypeTestCase;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\CallbackFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Query\DoctrineOrmProxyQueryInterface;

class CallbackFilterTypeTest extends FilterTypeTestCase
{
    public function testFiltersCallsGivenCallback(): void
    {
        $expectedQuery = $this->createMock(DoctrineOrmProxyQueryInterface::class);
        $expectedData = $this->createMock(FilterData::class);

        $filter = $this->createNamedFilter('test', [
            'callback' => function (DoctrineOrmProxyQueryInterface $query, FilterData $data) use ($expectedQuery, $expectedData) {
                $this->assertEquals($expectedQuery, $query);
                $this->assertEquals($expectedData, $data);
            },
        ]);

        $filter->apply($expectedQuery, $expectedData);
    }

    protected function getTestedType(): string
    {
        return CallbackFilterType::class;
    }
}
