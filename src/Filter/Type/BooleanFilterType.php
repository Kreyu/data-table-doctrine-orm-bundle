<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type;

use Doctrine\ORM\Query\Expr;
use Kreyu\Bundle\DataTableBundle\Exception\InvalidArgumentException;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\Operator;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

class BooleanFilterType extends AbstractDoctrineOrmFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => ChoiceType::class,
                'supported_operators' => [
                    Operator::Equals,
                    Operator::NotEquals,
                ],
                'active_filter_formatter' => function (FilterData $data) {
                    return new TranslatableMessage($data->getValue() ? 'Yes' : 'No', domain: 'KreyuDataTable');
                },
            ])
            ->addNormalizer('form_options', function (Options $options, array $value): array {
                if (ChoiceType::class !== $options['form_type']) {
                    return $value;
                }

                return $value + [
                    'choices' => ['Yes' => true, 'No' => false],
                    'choice_translation_domain' => 'KreyuDataTable',
                ];
            })
        ;
    }

    protected function createComparison(FilterData $data, Expr $expr): mixed
    {
        return match ($data->getOperator()) {
            Operator::Equals => $expr->eq(...),
            Operator::NotEquals => $expr->neq(...),
            default => throw new InvalidArgumentException('Operator not supported'),
        };
    }
}
