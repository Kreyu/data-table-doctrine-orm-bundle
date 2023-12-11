<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type;

use Kreyu\Bundle\DataTableBundle\Filter\Type\AbstractFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\ExpressionTransformer\ExpressionTransformerInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DoctrineOrmFilterType extends AbstractFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'auto_alias_resolving' => true,
                'expression_transformers' => [],
                'trim' => false,
                'lower' => false,
                'upper' => false,
            ])
            ->setAllowedTypes('auto_alias_resolving', 'bool')
            ->setAllowedTypes('expression_transformers', ExpressionTransformerInterface::class.'[]')
            ->setAllowedTypes('trim', 'bool')
            ->setAllowedTypes('lower', 'bool')
            ->setAllowedTypes('upper', 'bool')
        ;
    }
}
