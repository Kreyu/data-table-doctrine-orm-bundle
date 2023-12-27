<?php

declare(strict_types=1);

namespace Kreyu\Bundle\DataTableDoctrineOrmBundle\Filter\Type;

use Doctrine\ORM\Query\Expr;
use Doctrine\Persistence\ManagerRegistry;
use Kreyu\Bundle\DataTableBundle\Exception\InvalidArgumentException;
use Kreyu\Bundle\DataTableBundle\Filter\FilterBuilderInterface;
use Kreyu\Bundle\DataTableBundle\Filter\FilterData;
use Kreyu\Bundle\DataTableBundle\Filter\FilterInterface;
use Kreyu\Bundle\DataTableBundle\Filter\Operator;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccess;

class EntityFilterType extends AbstractDoctrineOrmFilterType
{
    public function __construct(
        private readonly ?ManagerRegistry $managerRegistry = null,
    ) {
    }

    public function buildFilter(FilterBuilderInterface $builder, array $options): void
    {
        $builder->setSupportedOperators([
            Operator::Equals,
            Operator::NotEquals,
            Operator::In,
            Operator::NotIn,
        ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'form_type' => EntityType::class,
                'choice_label' => null,
                'active_filter_formatter' => $this->getFormattedActiveFilterString(...),
            ])
            ->setAllowedTypes('choice_label', ['null', 'string', 'callable'])
        ;

        // The persistence feature is saving only the identifier of the entity,
        // therefore, the EntityType requires "choice_value" option with a name of the entity identifier field.
        if (null !== $this->managerRegistry) {
            $resolver->addNormalizer('form_options', function (Options $options, array $value) {
                if (EntityType::class !== $options['form_type'] || null === $class = $value['class'] ?? null) {
                    return $value;
                }

                $identifiers = $this->managerRegistry
                    ->getManagerForClass($class)
                    ?->getClassMetadata($class)
                    ->getIdentifier() ?? [];

                if (1 === count($identifiers)) {
                    $value += ['choice_value' => reset($identifiers)];
                }

                return $value;
            });
        }
    }

    private function getFormattedActiveFilterString(FilterData $data, FilterInterface $filter, array $options): string
    {
        $choiceLabel = $options['choice_label'];

        if (is_string($choiceLabel)) {
            return PropertyAccess::createPropertyAccessor()->getValue($data->getValue(), $choiceLabel);
        }

        if (is_callable($choiceLabel)) {
            return $choiceLabel($data->getValue());
        }

        return (string) $data->getValue();
    }
}
