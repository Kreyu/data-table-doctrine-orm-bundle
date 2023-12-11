# Expression Transformers

Expression transformers provide a way to extend Doctrine DQL expressions before they are executed by a filter handler.

- [Built-in Expression Transformers](#built-in-expression-transformers)
- [Creating a Custom Expression Transformer](#creating-a-custom-expression-transformer)

## Built-in Expression Transformers

- `TrimExpressionTransformer` - wraps the expression in the `TRIM()` function
- `LowerExpressionTransformer` - wraps the expression in the `LOWER()` function
- `UpperExpressionTransformer` - wraps the expression in the `UPPER()` function
- `CallbackExpressionTransformer` - allows transforming the expression using the callback function

The expression transformers can be passed using the `expression_transformers` option:

```php
use App\DataTable\Filter\ExpressionTransformer\UnaccentExpressionTransformer;
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\LowerExpressionTransformer;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\TrimExpressionTransformer;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\TextFilterType;

class ProductDataTableType extends AbstractDataTableType
{
    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addFilter('name', TextFilterType::class, [
                'expression_transformers' => [
                    new LowerExpressionTransformer(),
                    new TrimExpressionTransformer(),
                ],
            ])
        ;
    }
}
```

> [!TIP]
> The transformers are called in the same order as they are passed.

For easier usage, part of the built-in transformers can be enabled using the `trim`, `lower` and `upper` filter options:

```php
use Kreyu\Bundle\DataTableBundle\Type\AbstractDataTableType;
use Kreyu\Bundle\DataTableBundle\DataTableBuilderInterface;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\TextFilterType;

class ProductDataTableType extends AbstractDataTableType
{
    public function buildDataTable(DataTableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addFilter('name', TextFilterType::class, [
                'trim' => true,
                'lower' => true,
                'upper' => true,
                // the expression results in UPPER(LOWER(TRIM(...))))
            ])
        ;
    }
}
```

> [!IMPORTANT]  
> When using the `trim`, `lower` or `upper` options, their transformers are called **before** those passed in the `expression_transformers` option.

## Creating a Custom Expression Transformer

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

Thanks to that, the user can specify which side of the expression should be transformed.
The `transformLeftExpr()` and `transformRightExpr()` methods are called only when necessary.

To use the created expression transformer, pass it as the `expression_transformers` filter type option:

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
                'expression_transformers' => [
                    new UnaccentExpressionTransformer(),
                ],
            ])
        ;
    }
}
```