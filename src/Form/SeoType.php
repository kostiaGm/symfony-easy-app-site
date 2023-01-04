<?php

namespace App\Form;

use App\Entity\Seo;
use App\Entity\SeoItem;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

class SeoType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        if (!in_array('entity', $options['exclude'])) {
            $builder->add('entity');
        }

        if (!in_array('entityId', $options['exclude'])) {
            $builder->add('entityId');
        }

        $builder->add('items', TypeByConfig::class, [
            'configName' => 'seo',
            'label' => false
        ])->addEventListener(FormEvents::POST_SUBMIT, function (FormEvent $event) {
            $data = $event->getData();
            $dataItems = $data->getItems();

            foreach ($dataItems as $key => $item) {
                $newItem = new SeoItem();

                if ($item instanceof SeoItem) {
                    $newItem = $item;
                } else {
                    $newItem->setType($key);
                    $newItem->setContent($item);
                }

                $data->addItem($newItem);
                $dataItems[] = $newItem;
                unset($dataItems[$key], $newItem);
            }

            $event->setData($data);

        });
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults([
            'data_class' => Seo::class,
            'exclude' => []
        ]);
    }

}
