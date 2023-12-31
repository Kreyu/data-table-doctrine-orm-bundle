<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Handler;

use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableBundle\Filter\Operator;
use Kreyu\Bundle\DataTableBundle\Query\ProxyQueryInterface;

class DateRangeFilterHandler extends DoctrineOrmFilterHandler
{
    public function handle(ProxyQueryInterface $query, FilterData $data, FilterInterface $filter): void
    {
        $value = $data->getValue();

        $valueFrom = $value['from'] ?? null;
        $valueTo = $value['to'] ?? null;

        if ($valueFrom) {
            $valueFrom = \DateTime::createFromInterface($valueFrom);
            $valueFrom->setTime(0, 0);
        }

        if ($valueTo) {
            $valueTo = \DateTime::createFromInterface($valueTo)->modify('+1 day');
            $valueTo->setTime(0, 0);
        }

        $data = clone $data;

        if ($valueFrom && $valueTo) {
            $data->setValue(['from' => $valueFrom, 'to' => $valueTo]);
            $data->setOperator(Operator::Between);
        } elseif ($valueFrom) {
            $data->setValue($valueFrom);
            $data->setOperator(Operator::GreaterThanEquals);
        } elseif ($valueTo) {
            $data->setValue($valueTo);
            $data->setOperator(Operator::LessThan);
        }

        parent::handle($query, $data, $filter);
    }
}
