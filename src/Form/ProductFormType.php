<?php

namespace App\Form;

use App\Entity\Product;
use App\Entity\Role;
use App\Entity\User;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class ProductFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name', TextType::class, [
                'constraints' => [
                    new NotBlank(["message" => "Le nom ne peut pas être vide !"]),
                    new Length([
                        "min" => 3,
                        "max" => 50,
                        "minMessage" => "Le nom doit faire entre 3 et 50 caractères !",
                        "maxMessage" => "Le nom doit faire entre 3 et 50 caractères !"
                    ])
                ]
            ])
            ->add('barcode', TextType::class, [
                'constraints' => [
                    new NotBlank(["message" => "Le code-barre ne peut pas être vide !"]),
                    new Length([
                        "min" => 5,
                        "max" => 5,
                        "minMessage" => "Le code-barre doit faire exactement 5 caractères !",
                        "maxMessage" => "Le code-barre doit faire exactement 5 caractères !"
                    ])
                ]
            ])
            ->add('amount', TextType::class, [
                'constraints' => [
                    new NotBlank(["message" => "La quantité ne peut pas être vide !"]),
                ]
            ])
            ->add('price', NumberType::class, [
                'constraints' => [
                    new NotBlank(["message" => "La quantité ne peut pas être vide !"]),
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Product::class,
        ]);
    }
}
