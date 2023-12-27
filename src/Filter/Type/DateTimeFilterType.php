<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type;

use Kreyu\Bundle\DataTableBundle\Filter\FilterBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableBundle\Filter\Operator;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

class DateTimeFilterType extends AbstractDoctrineOrmFilterType
{
    public function buildFilter(FilterBuilderInterface $builder, array $options): void
    {
        $builder->setSupportedOperators([
            Operator::Equals,
            Operator::NotEquals,
            Operator::GreaterThan,
            Operator::GreaterThanEquals,
            Operator::LessThan,
            Operator::LessThanEquals,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => DateTimeType::class,
                'active_filter_formatter' => $this->getFormattedActiveFilterString(...),
            ])
            ->addNormalizer('form_options', function (Options $options, array $value): array {
                if (DateTimeType::class !== $options['form_type']) {
                    return $value;
                }

                return $value + ['widget' => 'single_text'];
            })
        ;
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
