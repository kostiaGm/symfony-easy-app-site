<?php

namespace App\Form;

use App\Entity\Seo;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class TypeByConfig extends AbstractType
{
    private ParameterBagInterface $params;


    public function __construct(ParameterBagInterface $params)
    {
        $this->params = $params;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $config = $this->params->get($options['configName']) ?? [];

        $builder->
        addEventListener(FormEvents::POST_SET_DATA, function (FormEvent $event) use ($config) {
            $form = $event->getForm();
            $data = $event->getData()->getValues();


            foreach ($config as $item) {
                if (empty($item['title'])) {
                    continue;
                }

                $val = '';
                foreach ($data as $value) {
                    if ($item['title'] == $value->getType()) {
                        $val = $value->getContent();
                    }
                }

                $form->add(
                    $item['title'],
                    $item['type'] ?? TextType::class,
                    $item['options'] ?? [
                        'required' => $item['options']['required'] ?? false,
                        'data' => $val

                    ]
                );
            }

        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'configName' => '',
        ]);
    }
}