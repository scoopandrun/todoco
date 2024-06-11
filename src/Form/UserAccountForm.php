<?php

namespace App\Form;

use App\DTO\UserInformationDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class UserAccountForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('username', TextType::class, [
                'label' => "Nom d'utilisateur",
                'required' => true,
            ])
            ->add('email', EmailType::class, [
                'label' => 'Adresse email',
                'required' => true,
            ])
            ->add('currentPassword', PasswordType::class, [
                'required' => false,
                'label' => 'Mot de passe actuel',
                'attr' => [
                    'autocomplete' => 'current-password',
                ],
            ])
            ->add('newPassword', NewPasswordType::class, [
                'required' => false,
            ])
            ->add('roles', RolesType::class, []);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => UserInformationDTO::class,
            'validation_groups' => ['account_update'],
        ]);
    }
}
