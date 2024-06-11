<?php

namespace App\Form;

use App\DTO\UserInformationDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationForm extends AbstractType
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
            ->add('newPassword', NewPasswordType::class, [
                'required' => true,
                'label' => 'Mot de passe',
            ])
            ->add('roles', RolesType::class, []);
    }

    #[\Override]
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserInformationDTO::class,
            'validation_groups' => ['registration'],
        ]);
    }
}
