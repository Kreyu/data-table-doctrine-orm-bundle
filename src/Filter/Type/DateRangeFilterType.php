<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type;

use Kreyu\Bundle\DataTableBundle\Filter\FilterBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Filter\Form\Type\DateRangeType;
use Kreyu\Bundle\DataTableBundle\Filter\Operator;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\EventListener\TransformDateRangeFilterData;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Formatter\DateRangeActiveFilterFormatter;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateRangeFilterType extends AbstractDoctrineOrmFilterType
{
    public function buildFilter(FilterBuilderInterface $builder, array $options): void
    {
        $builder
            ->setOperatorSelectable(false)
            ->setDefaultOperator(Operator::Between)
        ;

        $builder
            ->addEventSubscriber(new TransformDateRangeFilterData())
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => DateRangeType::class,
                'active_filter_formatter' => new DateRangeActiveFilterFormatter(),
            ])
        ;
    }
}
