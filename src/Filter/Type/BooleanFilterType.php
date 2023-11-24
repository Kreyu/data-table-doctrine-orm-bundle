<?php

declare(strict_types=1);

namespace Kreyu\Bridge\DataTableDoctrineOrm\Filter\Type;

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
                'active_filter_formatter' => $this->formatActiveValue(...),
            ])
            ->addNormalizer('form_options', function (Options $options, mixed $value) {
                if (ChoiceType::class !== $options['form_type']) {
                    return $value;
                }

                return $value + [
                    'choices' => ['yes' => true, 'no' => false],
                    'choice_label' => function (bool $choice, string $key) {
                        return t(ucfirst($key), domain: 'KreyuDataTable');
                    },
                ];
            })
        ;
    }

    protected function getOperatorExpression(string $queryPath, string $parameterName, Operator $operator, Expr $expr): object
    {
        $expression = match ($operator) {
            Operator::Equals => $expr->eq(...),
            Operator::NotEquals => $expr->neq(...),
            default => throw new InvalidArgumentException('Operator not supported'),
        };

        return $expression($queryPath, ":$parameterName");
    }

    private function formatActiveValue(FilterData $data): mixed
    {
        $value = $data->getValue() ? 'Yes' : 'No';

        if (class_exists(TranslatableMessage::class)) {
            $value = new TranslatableMessage($value, domain: 'KreyuDataTable');
        }

        return $value;
    }
}
