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
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

class DateRangeFilterType extends AbstractDoctrineOrmFilterType implements FilterHandlerInterface
{
    public function buildFilter(FilterBuilderInterface $builder, array $options): void
    {
        $builder->setHandler($this);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => DateRangeType::class,
                'active_filter_formatter' => $this->getFormattedActiveFilterString(...),
            ])
            ->addNormalizer('empty_data', function (Options $options, FilterData $value): FilterData {
                if (DateRangeType::class !== $options['form_type']) {
                    return $value;
                }

                $value->setValue(['from' => '', 'to' => '']);

                return $value;
            })
        ;
    }

    public function handle(ProxyQueryInterface $query, FilterData $data, FilterInterface $filter): void
    {
        $value = $data->getValue();

        if (is_array($value)) {
            return;
        }

        $handler = $filter->getConfig()->getHandler();

        if (null !== $dateFrom = $value['from'] ?? null) {
            $dateFrom = \DateTimeImmutable::createFromInterface($dateFrom);
            $dateFrom->setTime(0, 0);

            $handler->handle($query, new FilterData($dateFrom, Operator::GreaterThanEquals), $filter);
        }

        if (null !== $dateTo = $value['to'] ?? null) {
            $valueTo = \DateTimeImmutable::createFromInterface($dateTo)->modify('+1 day');
            $valueTo->setTime(0, 0);

            $handler->handle($query, new FilterData($dateTo, Operator::LessThan), $filter);
        }
    }

    protected function createComparison(FilterData $data, Expr $expr): mixed
    {
        return match ($data->getOperator()) {
            Operator::GreaterThanEquals => $expr->gte(...),
            Operator::LessThan => $expr->lt(...),
            default => throw new InvalidArgumentException('Operator not supported'),
        };
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
