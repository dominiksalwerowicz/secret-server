<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\DateTimeType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

class SecretAddForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('secret', TextType::class, [
                'constraints' => [
                    new Assert\NotNull(),
                ],
            ])
            ->add('expireAfterViews', IntegerType::class, [
                'constraints' => [
                    new Assert\NotNull(),
                    new Assert\Positive(),
                ],
            ])
            ->add('expireAfter', DateTimeType::class, [
                'widget' => 'single_text',
                'constraints' => [
                    new Assert\NotNull(),
                ],
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'csrf_protection' => false,
        ]);
    }
}