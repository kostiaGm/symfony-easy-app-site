<?php

namespace App\Form;

use App\Entity\Page;
use App\Entity\PageFilter;
use App\Repository\PageRepository;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

class PageFilterType extends AbstractType
{
    private PageRepository $pageRepository;

    public function __construct(PageRepository $pageRepository)
    {
        $this->pageRepository = $pageRepository;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        foreach ($options['filterItems'] as $field => $filterItem) {
            switch ($field) {

                case "name":
                case "updatedAt":
                case "createdAt":
                    $builder->add($field, TextType::class, [
                        'required' => false,
                        'attr' => [
                            'autocomplete' => 'off'
                        ]
                    ]);

                    break;

                case "image":

                    $builder->add($field, ChoiceType::class, [
                        'choices' => PageFilter::IMAGES,

                        'expanded' => true,
                        //'multiple' => true
                    ]);

                    break;

                case "status":

                    $statuses = array_merge(
                        ['All' => PageFilter::FILTER_ANY_IMAGES],
                        array_flip(Page::getStatuses())
                    );


                    $builder->add($field, ChoiceType::class, [
                        'choices' => $statuses,
                        'expanded' => true
                    ]);

                    break;


                default:
                    $builder->add($field, ChoiceType::class, [
                        'choices' => $filterItem,
                        'expanded' => true,
                        'multiple' => true,
                    ]);
                ;
            }

        }
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => PageFilter::class,
            'siteId' => 0,
            'fields' => [],
            'filterItems' => []
        ]);
    }
}
