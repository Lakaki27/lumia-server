<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RoleFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(["message" => "Le nom ne peut pas être vide !"]),
                    new Length([
                        "min" => 5,
                        "max" => 50,
                        "minMessage" => "Le nom doit faire entre 5 et 50 caractères !",
                        "maxMessage" => "Le nom doit faire entre 5 et 50 caractères !"
                    ])
                ]
            ])
            ->add('slug', TextType::class, [
                'constraints' => [
                    new NotBlank(["message" => "Le descriptif ne peut pas être vide !"]),
                    new Length([
                        "min" => 5,
                        "max" => 50,
                        "minMessage" => "Le descriptif doit faire entre 5 et 50 caractères !",
                        "maxMessage" => "Le descriptif doit faire entre 5 et 50 caractères !"
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Role::class,
        ]);
    }
}
