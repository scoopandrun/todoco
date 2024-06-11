<?php

namespace App\Form;

use App\DTO\RolesDTO;
use App\Entity\Task;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RolesType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('roles', ChoiceType::class, [
                'choices' => [
                    'Utilisateur' => 'ROLE_USER',
                    'Administrateur' => 'ROLE_ADMIN',
                ],
                'expanded' => true,
                'label' => $options['label'],
            ])
            ->get('roles')
            ->addModelTransformer(new CallbackTransformer(
                fn (?array $rolesArray): ?string => count($rolesArray ?? []) ? $rolesArray[0] : null,
                fn (?string $rolesString): ?array  => is_string($rolesString) ? [$rolesString] : null,
            ));
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => RolesDTO::class,
            'label' => 'Rôle',
        ]);

        $resolver->setAllowedTypes('label', 'string');
    }
}
