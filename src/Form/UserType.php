<?php

namespace App\Form;

use App\Entity\User;
use App\Validator\Constraints\PasswordRequirements;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => "Nom d'utilisateur",
                'required' => true,
                'attr' => [
                    'autocomplete' => 'username',
                ],
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'required' => true,
                'attr' => [
                    'autocomplete' => 'email',
                ],
            ])
            ->add('currentPassword', PasswordType::class, [
                'required' => false,
                'label' => 'Mot de passe actuel',
                'attr' => [
                    'autocomplete' => 'current-password',
                ],
            ])
            ->add('newPassword', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => $options['new_password_required'],
                'invalid_message' => 'Les mots de passe de correspondent pas.',
                'first_options' => [
                    'label' => $options['new_password_label'],
                    'help' => sprintf('Minimum %d caractères', PasswordRequirements::MIN_LENGTH),
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'min' => PasswordRequirements::MIN_LENGTH,
                        'max' => PasswordRequirements::MAX_LENGTH,
                    ],

                ],
                'second_options' => [
                    'label' => 'Retaper le mot de passe',
                    'attr' => [
                        'autocomplete' => 'new-password',
                        'min' => PasswordRequirements::MIN_LENGTH,
                        'max' => PasswordRequirements::MAX_LENGTH,
                    ],
                ],
            ])
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
                fn (?array $rolesArray): string => count($rolesArray ?? []) ? $rolesArray[0] : 'ROLE_USER',
                fn (?string $rolesString): array  => is_string($rolesString) ? [$rolesString] : [],
            ));
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
            'new_password_label' => 'Nouveau mot de passe',
            'new_password_required' => false,
        ]);

        $resolver->setAllowedTypes('new_password_label', 'string');
        $resolver->setAllowedTypes('new_password_required', 'bool');
    }
}
