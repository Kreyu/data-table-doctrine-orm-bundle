<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Tests\Filter\Type;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\QueryBuilder;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\Operator;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\DateRangeFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Query\DoctrineOrmProxyQuery;
use PHPUnit\Framework\Attributes\DataProvider;

class DateRangeFilterTypeTest extends DoctrineOrmFilterTypeTestCase
{
    public static function operatorExpressionProvider(): iterable
    {
        $expr = new Expr();

        yield [Operator::Equals, $expr->andX(...)];
    }

    #[DataProvider('operatorExpressionProvider')]
    public function testApplyingFilter(Operator $operator, callable $expression, mixed $value = ''): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('getRootAliases')->willReturn([]);
        $queryBuilder->method('expr')->willReturn(new Expr());

        $queryBuilder->expects($this->once())
            ->method('andWhere')
            ->with($expression(
                (new Expr())->gte('test', ':test_0_from'),
                (new Expr())->lt('test', ':test_0_to'),
            ))
        ;

        $value = ['from' => new \DateTime('yesterday'), 'to' => new \DateTime('tomorrow')];

        $queryBuilder->expects($matcher = $this->exactly(2))
            ->method('setParameter')
            ->with(
                $this->callback(
                    fn (string $parameterName) => match ($matcher->numberOfInvocations()) {
                        1 => 'test_0_from' === $parameterName,
                        2 => 'test_0_to' === $parameterName,
                        default => false,
                    },
                ),
                $this->callback(
                    fn ($parameterValue) => match ($matcher->numberOfInvocations()) {
                        1 => $parameterValue == $value['from']->setTime(0, 0),
                        2 => $parameterValue == $value['to']->modify('+1 day')->setTime(0, 0),
                        default => false,
                    },
                ),
            )
            ->willReturn($queryBuilder)
        ;

        $query = $this->createMock(DoctrineOrmProxyQuery::class);
        $query->method('getQueryBuilder')->willReturn($queryBuilder);

        $filter = $this->createNamedFilter('test');
        $filter->apply($query, new FilterData($value, $operator));
    }

    protected function getTestedType(): string
    {
        return DateRangeFilterType::class;
    }
}
