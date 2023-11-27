<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type;

use Doctrine\ORM\Query\Expr;
use Kreyu\Bundle\DataTableBundle\Exception\InvalidArgumentException;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\Operator;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\TranslatableMessage;

class BooleanFilterType extends AbstractDoctrineOrmFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => ChoiceType::class,
                'form_options' => [
                    'choices' => ['yes' => true, 'no' => false],
                    'choice_label' => function (bool $choice, string $key) {
                        return $this->getTranslatableMessage(ucfirst($key));
                    },
                ],
                'supported_operators' => [
                    Operator::Equals,
                    Operator::NotEquals,
                ],
                'active_filter_formatter' => function (FilterData $data) {
                    return $this->getTranslatableMessage($data->getValue() ? 'Yes' : 'No');
                },
            ])
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

    private function getTranslatableMessage(string $value): string|TranslatableMessage
    {
        if (class_exists(TranslatableMessage::class)) {
            return new TranslatableMessage($value, domain: 'KreyuDataTable');
        }

        return $value;
    }
}
