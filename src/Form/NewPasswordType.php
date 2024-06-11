<?php

namespace App\Form;

use App\DTO\NewPasswordDTO;
use App\Validator\Constraints\PasswordRequirements;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class NewPasswordType extends AbstractType
{
    #[\Override]
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('password', RepeatedType::class, [
                'type' => PasswordType::class,
                'required' => $options['required'],
                'invalid_message' => 'Les mots de passe de correspondent pas.',
                'first_options' => [
                    'label' => $options['label'],
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
            ]);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => NewPasswordDTO::class,
            'label' => 'Nouveau mot de passe',
            'required' => false,
        ]);

        $resolver->setAllowedTypes('label', 'string');
        $resolver->setAllowedTypes('required', 'bool');
    }
}
