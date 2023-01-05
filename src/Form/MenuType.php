<?php

namespace App\Form;

use Symfony\Component\Form\AbstractType;
use App\Entity\Menu;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class MenuType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')

            ->add('url', TextType::class, [
                    'required' => false
            ])
            ->add('route', TextType::class, [
                'required' => false,
            ])
            ->add('isTopMenu', CheckboxType::class, [
                'required' => false,
            ])
            ->add('isLeftMenu', CheckboxType::class, [
                'required' => false,
            ])
            ->add('isBottomMenu', CheckboxType::class, [
                'required' => false,
            ])

            ->add('type', ChoiceType::class, [
                'choices' => array_flip(Menu::TYPES)
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver->setDefaults([
                'data_class' => Menu::class,
            ]
        );
    }
}