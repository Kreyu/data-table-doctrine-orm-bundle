<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Event;

final class DoctrineOrmFilterEvents
{
    public const PRE_SET_PARAMETERS = 'kreyu_doctrine_orm_data_table.filter.pre_set_parameters';

    public const PRE_APPLY_EXPRESSION = 'kreyu_doctrine_orm_data_table.filter.pre_apply_expression';

    private function __construct()
    {
    }
}
