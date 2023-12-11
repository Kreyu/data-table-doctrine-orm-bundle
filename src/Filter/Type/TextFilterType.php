<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type;

use Doctrine\ORM\Query\Expr;
use Kreyu\Bundle\DataTableBundle\Exception\InvalidArgumentException;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\Operator;
use Kreyu\Bundle\DataTableDoctrineOrmBundle\Query\DoctrineOrmProxyQueryInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TextFilterType extends AbstractDoctrineOrmFilterType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'default_operator' => Operator::Contains,
                'supported_operators' => [
                    Operator::Equals,
                    Operator::NotEquals,
                    Operator::Contains,
                    Operator::NotContains,
                    Operator::StartsWith,
                    Operator::EndsWith,
                ],
            ])
        ;
    }

    protected function createComparison(FilterData $data, Expr $expr): mixed
    {
        return match ($data->getOperator()) {
            Operator::Equals => $expr->eq(...),
            Operator::NotEquals => $expr->neq(...),
            Operator::Contains, Operator::StartsWith, Operator::EndsWith => $expr->like(...),
            Operator::NotContains => $expr->notLike(...),
            default => throw new InvalidArgumentException('Operator not supported'),
        };
    }

    protected function getParameterValue(FilterData $data): mixed
    {
        $value = $data->getValue();

        return match ($data->getOperator()) {
            Operator::Contains => "%$value%",
            Operator::StartsWith => "$value%",
            Operator::EndsWith => "%$value",
            default => $value,
        };
    }
}
