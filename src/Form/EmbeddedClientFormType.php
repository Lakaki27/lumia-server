<?php

namespace App\Form;

use App\Entity\EmbeddedClient;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\Length;
use Symfony\Component\Validator\Constraints\NotBlank;

class EmbeddedClientFormType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('serial', TextType::class, [
                'constraints' => [
                    new NotBlank(),
                    new Length([
                        "min" => 16,
                        "max" => 16,
                        "minMessage" => "Le numéro de série doit faire exactement 16 caractères !",
                        "maxMessage" => "Le numéro de série doit faire exactement 16 caractères !"
                    ])
                ]
            ]);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => EmbeddedClient::class,
        ]);
    }
}
