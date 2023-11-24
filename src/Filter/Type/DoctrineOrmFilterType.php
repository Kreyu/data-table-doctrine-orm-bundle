<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type;

use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableBundle\Filter\Type\AbstractFilterType;
use Kreyu\Bundle\DataTableBundle\Query\ProxyQueryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class DoctrineOrmFilterType extends AbstractFilterType
{
    public function apply(ProxyQueryInterface $query, FilterData $data, FilterInterface $filter, array $options): void
    {
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'auto_alias_resolving' => true,
                'trim' => false,
                'lower' => false,
                'upper' => false,
            ])
            ->setAllowedTypes('auto_alias_resolving', 'bool')
            ->setAllowedTypes('trim', 'bool')
            ->setAllowedTypes('lower', 'bool')
            ->setAllowedTypes('upper', 'bool')
        ;
    }
}
