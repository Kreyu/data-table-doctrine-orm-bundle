<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Tests\Query;

use Doctrine\ORM\Query\Expr\Select;
use Doctrine\ORM\QueryBuilder;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Query\AliasResolver;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class AliasResolverTest extends TestCase
{
    private AliasResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new AliasResolver();
    }

    public function testResolvingShouldAddRootAlias(): void
    {
        $queryBuilder = $this->createQueryBuilderMock('alias');

        $queryPath = $this->resolver->resolve('foo', $queryBuilder);

        $this->assertEquals('alias.foo', $queryPath);
    }

    public function testResolvingWithDotInQueryPathShouldNotAddRootAlias(): void
    {
        $queryBuilder = $this->createQueryBuilderMock('alias');

        $queryPath = $this->resolver->resolve('foo.bar', $queryBuilder);

        $this->assertEquals('foo.bar', $queryPath);
    }

    public function testResolvingWithQueryPathThatExistsInSelectDQLPartShouldNotAddRootAlias(): void
    {
        $queryBuilder = $this->createQueryBuilderMock('alias');

        $queryBuilder->method('getDQLPart')->with('select')->willReturn([
            new Select(['test AS foo']),
        ]);

        $queryPath = $this->resolver->resolve('foo', $queryBuilder);

        $this->assertEquals('foo', $queryPath);
    }

    public function testResolvingWithQueryPathThatExistsInHiddenSelectDQLPartShouldNotAddRootAlias(): void
    {
        $queryBuilder = $this->createQueryBuilderMock('alias');

        $queryBuilder->method('getDQLPart')->with('select')->willReturn([
            new Select(['test AS HIDDEN foo']),
        ]);

        $queryPath = $this->resolver->resolve('foo', $queryBuilder);

        $this->assertEquals('foo', $queryPath);
    }

    private function createQueryBuilderMock(string $rootAlias): QueryBuilder&MockObject
    {
        $queryBuilder = $this->createMock(QueryBuilder::class);
        $queryBuilder->method('getRootAliases')->willReturn([$rootAlias]);

        return $queryBuilder;
    }
}
