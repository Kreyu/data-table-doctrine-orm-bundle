<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type;

use Doctrine\ORM\Query\Expr;
use Kreyu\Bundle\DataTableBundle\Exception\InvalidArgumentException;
use Kreyu\Bundle\DataTableBundle\Filter\FilterBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterHandlerInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableBundle\Filter\Form\Type\DateRangeType;
use Kreyu\Bundle\DataTableBundle\Filter\Operator;
use Kreyu\Bundle\DataTableBundle\Query\ProxyQueryInterface;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\DoctrineOrmFilterHandler;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

class DateRangeFilterType extends AbstractDoctrineOrmFilterType implements FilterHandlerInterface
{
    public function buildFilter(FilterBuilderInterface $builder, array $options): void
    {
        $builder->setHandler($this);
        $builder->setOperatorSelectable(false);
        $builder->setDefaultOperator(Operator::Between);
        $builder->setSupportedOperators([
            Operator::Between,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => DateRangeType::class,
                'active_filter_formatter' => $this->getFormattedActiveFilterString(...),
            ])
        ;
    }

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

        $data = new FilterData();

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

        $handler = new DoctrineOrmFilterHandler();
        $handler->handle($query, $data, $filter);
    }

    private function getFormattedActiveFilterString(FilterData $data): string|TranslatableMessage
    {
        $value = $data->getValue();

        $dateFrom = $value['from'];
        $dateTo = $value['to'];

        if (null !== $dateFrom && null === $dateTo) {
            return new TranslatableMessage('After %date%', ['%date%' => $dateFrom->format('Y-m-d')], 'KreyuDataTable');
        }

        if (null === $dateFrom && null !== $dateTo) {
            return new TranslatableMessage('Before %date%', ['%date%' => $dateTo->format('Y-m-d')], 'KreyuDataTable');
        }

        if ($dateFrom == $dateTo) {
            return $dateFrom->format('Y-m-d');
        }

        return $dateFrom->format('Y-m-d').' - '.$dateTo->format('Y-m-d');
    }
}
