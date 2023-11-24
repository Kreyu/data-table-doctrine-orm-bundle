<?php

use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\BooleanFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\CallbackFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\DateFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\DateRangeFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\DateTimeFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\DoctrineOrmFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\EntityFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\NumberFilterType;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type\TextFilterType;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

return static function (ContainerConfigurator $configurator) {
    $configurator->services()
        ->set('kreyu_data_table_doctrine_orm.filter.type.doctrine_orm', DoctrineOrmFilterType::class)
            ->tag('kreyu_data_table.filter.type')

        ->set('kreyu_data_table_doctrine_orm.filter.type.boolean', BooleanFilterType::class)
            ->tag('kreyu_data_table.filter.type')

        ->set('kreyu_data_table_doctrine_orm.filter.type.callback', CallbackFilterType::class)
            ->tag('kreyu_data_table.filter.type')

        ->set('kreyu_data_table_doctrine_orm.filter.type.date', DateFilterType::class)
            ->tag('kreyu_data_table.filter.type')

        ->set('kreyu_data_table_doctrine_orm.filter.type.date_range', DateRangeFilterType::class)
            ->tag('kreyu_data_table.filter.type')

        ->set('kreyu_data_table_doctrine_orm.filter.type.date_time', DateTimeFilterType::class)
            ->tag('kreyu_data_table.filter.type')

        ->set('kreyu_data_table_doctrine_orm.filter.type.entity', EntityFilterType::class)
            ->tag('kreyu_data_table.filter.type')

        ->set('kreyu_data_table_doctrine_orm.filter.type.number', NumberFilterType::class)
            ->tag('kreyu_data_table.filter.type')

        ->set('kreyu_data_table_doctrine_orm.filter.type.text', TextFilterType::class)
            ->tag('kreyu_data_table.filter.type')
    ;
};
