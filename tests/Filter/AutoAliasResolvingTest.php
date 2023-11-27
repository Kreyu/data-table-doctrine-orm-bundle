<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Tests\Filter\Type;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Comparison;
use Doctrine\ORM\QueryBuilder;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\Operator;
use Kreyu\Bundle\DataTableBundle\Test\Filter\FilterIntegrationTestCase;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\AbstractDoctrineOrmFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Query\DoctrineOrmProxyQuery;
use Symfony\Component\OptionsResolver\OptionsResolver;

class AutoAliasResolvingTest extends FilterIntegrationTestCase
{
    public function testAutoAliasResolvingEnabled(): void
    {
        $this->testAutoAliasResolving(true);
    }

    public function testAutoAliasResolvingDisabled(): void
    {
        $this->testAutoAliasResolving(false);
    }

    private function testAutoAliasResolving(bool $enabled): void
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('getRootAliases')->willReturn(['alias']);

        $queryBuilder->expects($this->once())
            ->method('andWhere')
            ->with($this->callback(function (Comparison $comparison) use ($enabled) {
                return $comparison->getLeftExpr() === ($enabled ? 'alias.test' : 'test');
            }))
            ->willReturn($queryBuilder);

        $query = $this->createMock(DoctrineOrmProxyQuery::class);
        $query->method('getQueryBuilder')->willReturn($queryBuilder);

        $filter = $this->factory->create(TestFilterType::class, ['auto_alias_resolving' => $enabled]);
        $filter->apply($query, new FilterData());
    }
}

class TestFilterType extends AbstractDoctrineOrmFilterType
{
    protected function getOperatorExpression(string $queryPath, string $parameterName, Operator $operator, Expr $expr): object
    {
        return $expr->eq($queryPath, ":$parameterName");
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefault('supported_operators', Operator::cases());
    }
}
