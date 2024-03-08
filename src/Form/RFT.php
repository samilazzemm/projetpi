<?php

namespace App\Form;

use App\Entity\User;
use Doctrine\DBAL\Types\TextType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class RegistrationFotmType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
        ->add('email', EmailType::class, [
            'attr' => [
                'class' => 'form-control'
            ],
            'label' => 'E-mail'
        ])
        ->add('username', TextType::class, [
            'attr' => [
                'class' => 'form-control'
            ],
            'label' => 'Username'
        ])
        ->add('name', TextType::class, [
            'attr' => [
                'class' => 'form-control'
            ],
            'label' => 'Name'
        ])
        ->add('password', TextType::class, [
            'attr' => [
                'class' => 'form-control'
            ],
            'label' => 'Paswword'
        ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
