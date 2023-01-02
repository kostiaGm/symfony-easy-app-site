<?php

namespace App\Form;

use App\Entity\Menu;
use App\Entity\Page;
use App\Entity\Site;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('name')
            ->add('preview', TextareaType::class, [
                'required' => false
            ])
            ->add('body', TextareaType::class, [
                'required' => false
            ])
            ->add('status', ChoiceType::class, [
                'choices' => array_flip(Page::getStatuses())
            ])

            ->add('menu', EntityType::class, [
                'class' => Menu::class,
                'choice_label' => 'name',
            ])
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Page::class,
        ]);
    }
}
