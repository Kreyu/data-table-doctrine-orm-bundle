<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type;

use Kreyu\Bundle\DataTableBundle\Filter\FilterBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Filter\Type\AbstractFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\EventListener\ApplyExpressionTransformers;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\ExpressionTransformerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DoctrineOrmFilterType extends AbstractFilterType
{
    public function buildFilter(FilterBuilderInterface $builder, array $options): void
    {
        $builder->addEventSubscriber(new ApplyExpressionTransformers());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'expression_transformers' => [],
                'trim' => false,
                'lower' => false,
                'upper' => false,
            ])
            ->setAllowedTypes('expression_transformers', ExpressionTransformerInterface::class.'[]')
            ->setAllowedTypes('trim', 'bool')
            ->setAllowedTypes('lower', 'bool')
            ->setAllowedTypes('upper', 'bool')
        ;
    }
}
