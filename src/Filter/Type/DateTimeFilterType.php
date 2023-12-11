<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type;

use Doctrine\ORM\Query\Expr;
use Kreyu\Bundle\DataTableBundle\Exception\InvalidArgumentException;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableBundle\Filter\Operator;
use Kreyu\Bundle\DataTableBundle\Query\ProxyQueryInterface;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateTimeFilterType extends AbstractDoctrineOrmFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => DateTimeType::class,
                'supported_operators' => [
                    Operator::Equals,
                    Operator::NotEquals,
                    Operator::GreaterThan,
                    Operator::GreaterThanEquals,
                    Operator::LessThan,
                    Operator::LessThanEquals,
                ],
                'active_filter_formatter' => $this->getFormattedActiveFilterString(...),
            ])
            ->addNormalizer('form_options', function (Options $options, array $value): array {
                if (DateTimeType::class !== $options['form_type']) {
                    return $value;
                }

                return $value + ['widget' => 'single_text'];
            })
            ->addNormalizer('empty_data', function (Options $options, FilterData $value): FilterData {
                if (DateTimeType::class !== $options['form_type']) {
                    return $value;
                }

                $widget = $options['form_options']['widget'] ?? null;

                if (in_array($widget, ['choice', 'text'])) {
                    $value->setValue(['day' => '', 'month' => '', 'year' => '']);
                }

                return $value;
            })
        ;
    }

    protected function createComparison(FilterData $data, Expr $expr): mixed
    {
        return match ($data->getOperator()) {
            Operator::Equals => $expr->eq(...),
            Operator::NotEquals => $expr->neq(...),
            Operator::GreaterThan => $expr->gt(...),
            Operator::GreaterThanEquals => $expr->gte(...),
            Operator::LessThan => $expr->lt(...),
            Operator::LessThanEquals => $expr->lte(...),
            default => throw new InvalidArgumentException('Operator not supported'),
        };
    }

    protected function getParameterValue(FilterData $data): \DateTimeInterface
    {
        $value = $data->getValue();

        if ($value instanceof \DateTimeInterface) {
            return $value;
        }

        if (is_string($value)) {
            return \DateTime::createFromFormat('Y-m-d\TH:i', $value);
        }

        if (is_array($value)) {
            return (new \DateTime())
                ->setDate(
                    year: (int) $value['date']['year'] ?: 0,
                    month: (int) $value['date']['month'] ?: 0,
                    day: (int) $value['date']['day'] ?: 0,
                )
                ->setTime(
                    hour: (int) $value['time']['hour'] ?: 0,
                    minute: (int) $value['time']['minute'] ?: 0,
                    second: (int) $value['time']['second'] ?: 0,
                )
            ;
        }

        throw new \InvalidArgumentException(sprintf('Unable to convert data of type "%s" to DateTime object.', get_debug_type($value)));
    }

    private function getFormattedActiveFilterString(FilterData $data, FilterInterface $filter, array $options): string
    {
        $value = $data->getValue();

        if ($value instanceof \DateTimeInterface) {
            $format = $options['form_options']['input_format'] ?? null;

            if (null === $format) {
                $format = 'Y-m-d H';

                if ($options['form_options']['with_minutes'] ?? true) {
                    $format .= ':i';
                }

                if ($options['form_options']['with_seconds'] ?? true) {
                    $format .= ':s';
                }
            }

            return $value->format($format);
        }

        return (string) $value;
    }
}
