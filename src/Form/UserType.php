<?php

namespace App\Form;

use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\Form\Extension\Core\Type\RepeatedType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\Image;


class UserType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $imageConstraints = [
            new Image([
                'maxSize' => '500K',
                'mimeTypes' => [ 'image/jpg' , 'image/jpeg', 'image/png' ]
            ])
        ];
        $builder
            ->add('email', EmailType::class)
            ->add('password', PasswordType::class, array('label' => 'Password'))
            ->add('name', TextType::class)
            ->add('status', ChoiceType::class, [
                    'choices'  => [
                        'Active' => 1,
                        'Not Active' => 0,
                    ],
                ])
            ->add('profileFile', FileType::class, ['mapped' => false, 'required' => false, 'constraints' => $imageConstraints])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
