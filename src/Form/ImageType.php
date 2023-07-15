<?php

namespace App\Form;

use App\Entity\Image;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints\File;

class ImageType extends AbstractType
{
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

        $builder
          /*  ->add('uploadImage', FileType::class, [
                'label' => 'Image ('.implode(', ', $config['allowFormats']).')',
                'required' => false,
                'constraints' => [
                    new File([
                        //     'maxSize' => $config['maxSize'],
                        //       'mimeTypes' => $config['mimeTypes'],
                        'mimeTypesMessage' => 'Please upload a valid document',
                    ])
                ]
            ])*/
            ->add('name')
            /*->add('width')
            ->add('height')*/
        ;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Image::class,
        ]);
    }
}
