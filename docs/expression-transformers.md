# Expression Transformers

Expression transformers provide a way to extend Doctrine DQL expressions before they are executed in the filtering process.

## Built-in Expression Transformers

- `TrimExpressionTransformer` - wraps the expression in the `TRIM()` function
- `LowerExpressionTransformer` - wraps the expression in the `LOWER()` function
- `UpperExpressionTransformer` - wraps the expression in the `UPPER()` function
- `CallbackExpressionTransformer` - allows transforming the expression using the callback function
- `ChainExpressionTransformer` - allows applying multiple expression transformers

**Note**: for easier usage, part of the built-in transformers can be enabled using the `trim`, `lower` and `upper` filter options.

## Creating Custom Expression Transformer

To create a custom expression transformer, create a new class that implements `ExpressionTransformerInterface`:

```php
namespace App\DataTable\Filter\ExpressionTransformer;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Comparison;
use Kreyu\Bundle\DataTableBundle\Exception\UnexpectedTypeException;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\ExpressionTransformerInterface;

class UnaccentExpressionTransformer implements ExpressionTransformerInterface
{
    public function transform(mixed $expression): mixed
    {
        if (!$expression instanceof Comparison) {
            throw new UnexpectedTypeException($expression, Comparison::class);
        }

        $leftExpr = sprintf('UNACCENT(%s)', (string) $expression->getLeftExpr());
        $rightExpr = sprintf('UNACCENT(%s)', (string) $expression->getRightExpr());
        
        // or use expression API:
        //
        // $leftExpr = new Expr\Func('UNACCENT', $expression->getLeftExpr());
        // $rightExpr = new Expr\Func('UNACCENT', $expression->getRightExpr());

        return new Comparison($leftExpr, $expression->getOperator(), $rightExpr);
    }
}
```

If you're sure that the expression transformer requires the expression to be a comparison (it will be in most cases),
you can extend the `AbstractComparisonExpressionTransformer` class which simplifies the definition:

```php
namespace App\DataTable\Filter\ExpressionTransformer;

use Doctrine\ORM\Query\Expr;
use Doctrine\ORM\Query\Expr\Comparison;
use Kreyu\Bundle\DataTableBundle\Exception\UnexpectedTypeException;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\AbstractComparisonExpressionTransformer;

class UnaccentExpressionTransformer extends AbstractComparisonExpressionTransformer
{
    protected function transformLeftExpr(mixed $leftExpr, Expr $expr): mixed
    {
        return sprintf('UNACCENT(%s)', (string) $leftExpr);
        
        // or use expression API: 
        // 
        // return new Expr\Func('UNACCENT', $leftExpr);
    }

    protected function transformRightExpr(mixed $rightExpr, Expr $expr): mixed
    {
        return sprintf('UNACCENT(%s)', (string) $rightExpr);
        
        // or use expression API: 
        //
        // return new Expr\Func('UNACCENT', $rightExpr);
    }
}
```

**Note**: the `AbstractComparisonExpressionTransformer` accepts two boolean arguments in the constructor:

- `transformLeftExpr` - defaults to `true`
- `transformRightExpr` - defaults to `true`

Thanks to that, the user can specify which side of the expression should be 
transformed - the `transformLeftExpr()` and `transformRightExpr()` methods are called only when necessary.

To use the created expression transformer, pass it as the `expression_transformer` filter type option:

```php
use App\DataTable\Filter\ExpressionTransformer\UnaccentExpressionTransformer;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\TextFilterType;

class ProductDataTableType extends AbstractDataTableType
{
    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addFilter('name', TextFilterType::class, [
                'expression_transformer' => new UnaccentExpressionTransformer(),
            ])
        ;
    }
}
```

If you wish to use multiple expression transformers, use the built-in `ChainExpressionTransformer` and pass transformers
as its constructor argument - they will be called in the same order as they are passed:

```php
use App\DataTable\Filter\ExpressionTransformer\UnaccentExpressionTransformer;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\TextFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\ChainExpressionTransformer;

class ProductDataTableType extends AbstractDataTableType
{
    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addFilter('name', TextFilterType::class, [
                'expression_transformer' => new ChainExpressionTransformer([
                    new UnaccentExpressionTransformer(),
                    new AnotherExpressionTransformer(),
                    // ...
                ]),
            ])
        ;
    }
}
```

## Built-in Expression Transformer Order

When using the `trim`, `lower` or `upper` options, the bundle will automatically create a `ChainExpressionTransformer`
applying their transformers **before** the transformer passed in the `expression_transformer` option:

```php
use App\DataTable\Filter\ExpressionTransformer\UnaccentExpressionTransformer;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\TextFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\ChainExpressionTransformer;

class ProductDataTableType extends AbstractDataTableType
{
    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addFilter('name', TextFilterType::class, [
                'lower' => true,
                'expression_transformer' => new UnaccentExpressionTransformer(),
                // the expression results in UNACCENT(LOWER(...))
            ])
        ;
    }
}
```

If you wish to change this behaviour, skip using the `trim`, `lower` or `upper` functions,
and explicitly pass their transformers in the `ChainExpressionTransformer`:

```php
use App\DataTable\Filter\ExpressionTransformer\UnaccentExpressionTransformer;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\TextFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\ChainExpressionTransformer;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\LowerExpressionTransformer;

class ProductDataTableType extends AbstractDataTableType
{
    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addFilter('name', TextFilterType::class, [
                'expression_transformer' => new ChainExpressionTransformer([
                    new LowerExpressionTransformer(),
                    new UnaccentExpressionTransformer(),
                ]),
                // the expression results in LOWER(UNACCENT(...))
            ])
        ;
    }
}
```