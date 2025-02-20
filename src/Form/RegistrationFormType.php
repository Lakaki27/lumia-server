<?php

namespace App\Form;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\RoleRepository;
use Symfony\Component\Form\AbstractType;
use App\Form\DataTransformer\RoleToStringTransformer;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class RegistrationFormType extends AbstractType
{
    private RoleRepository $roleRepository;

    public function __construct(RoleRepository $roleRepository)
    {
        $this->roleRepository = $roleRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('first_name', TextType::class, [
                'constraints' => [
                    new NotBlank(["message" => "Le prénom ne peut pas être vide !"]),
                    new Length([
                        "min" => 2,
                        "max" => 50,
                        "minMessage" => "Le numéro de série doit faire exactement 16 caractères !",
                        "maxMessage" => "Le numéro de série doit faire exactement 16 caractères !"
                    ])
                ]
            ])
            ->add('last_name', TextType::class, [
                'constraints' => [
                    new NotBlank(["message" => "Le nom ne peut pas être vide !"]),
                    new Length([
                        "min" => 2,
                        "max" => 50,
                        "minMessage" => "Le numéro de série doit faire exactement 16 caractères !",
                        "maxMessage" => "Le numéro de série doit faire exactement 16 caractères !"
                    ])
                ]
            ])
            ->add('email', EmailType::class, [
                'constraints' => [
                    new NotBlank(["message" => "L'email ne peut pas être vide !"]),
                    new Length([
                        "min" => 10,
                        "max" => 100,
                        "minMessage" => "Le numéro de série doit faire exactement 16 caractères !",
                        "maxMessage" => "Le numéro de série doit faire exactement 16 caractères !"
                    ])
                ]
            ])
            ->add('roles', ChoiceType::class, [
                'choices' => $this->roleRepository->findAll(),
                'required' => false,
                'multiple' => true,
                'choice_value' => 'id',
                'choice_label' => 'name',
                'choice_attr' => function ($role, $key, $index) {
                    return ['data-id' => $role->getId()];
                },
                "mapped" => false
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => User::class,
        ]);
    }
}
