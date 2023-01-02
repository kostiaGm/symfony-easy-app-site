<?php

namespace App\Form;

use App\Entity\Menu;
use App\Entity\Page;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PageType extends AbstractType
{
    private ParameterBagInterface $params;

    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $config = $this->params->get('image');

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
            ->add('uploadImage', FileType::class, [
                'label' => 'Image ('.implode(', ', $config['allowFormats']).')',
                'required' => false,
                'constraints' => [
                    new File([
                   //     'maxSize' => $config['maxSize'],
                 //       'mimeTypes' => $config['mimeTypes'],
                        'mimeTypesMessage' => 'Please upload a valid document',
                    ])
                ]
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
