<?php

namespace App\Form;

use App\Entity\Interfaces\NodeInterface;
use App\Entity\Menu;
use App\Entity\Page;
use App\Service\Traits\ActiveSiteTrait;
use App\Repository\MenuRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class PageType extends AbstractType
{
    use ActiveSiteTrait;
    private ParameterBagInterface $params;
    private RequestStack $request;

    public function __construct(ParameterBagInterface $params, RequestStack $request)
    {
        $this->params = $params;
        $this->request = $request;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $config = $this->params->get('image');
        $siteId = $this->getActiveSite()['id'] ?? 0;

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
                'empty_data' => '',
                'required' => false,

                'query_builder' => function (MenuRepository $menuRepository) use ($siteId) {
                    return $menuRepository->getAllMenuQueryBuilder($siteId);
                },
                'choice_label' => function(NodeInterface $data) {
                    $pad = '';
                    for ($i = 1; $i < $data->getLvl(); $i++) {
                        $pad .= '-';
                    }
                    return (!empty($pad) ? '|' : ''). $pad.$data->getName();
                }
            ])

            ->add('isOnMainPage', CheckboxType::class, [
                'required' => false
            ]) ->add('isPreview', CheckboxType::class, [
                'required' => false
            ])
            ->add('previewDeep',TextType::class, [
                'required' => false
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
