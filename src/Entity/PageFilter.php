<?php

namespace App\Entity;

use App\Entity\Interfaces\StatusInterface;
use App\Lib\Interfaces\FilterEntityInterface;
use Doctrine\DBAL\Types\DateIntervalType;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

class PageFilter implements FilterEntityInterface
{
    public const FILTER_WITH_IMAGE_ONLY = 'With images only';
    public const FILTER_WITHOUT_IMAGE_ONLY = 'Without images only';

    public const FILTER_NAME = "Name";
    public const FILTER_CREATED_AT = "Created";
    public const FILTER_UPDATED_AT = "Updated";
    public const FILTER_STATUS = "Status";

    public const IMAGES = [
        self::ITEM_ANY => self::ITEM_ANY,
        self::FILTER_WITH_IMAGE_ONLY => self::FILTER_WITH_IMAGE_ONLY,
        self::FILTER_WITHOUT_IMAGE_ONLY => self::FILTER_WITHOUT_IMAGE_ONLY
    ];

    private $image;

    private $name;

    private  $createdAt;

    private  $updatedAt;

    private $status;

    /**
     * @param string $fieldName
     * @return mixed
     */
    public function getFields(): array
    {
        return [
            'name' => [
                //     'default' => 'Name',
                'type' => ChoiceType::class,
                'options' => [
                    'data' => [],
                    'expanded' => true,
                    'multiple' => true
                ]

            ],
            'image' => [
                'query_builder' => function(QueryBuilder $queryBuilder, $value, $alias) {

                     if ($value == self::FILTER_WITH_IMAGE_ONLY) {
                         $queryBuilder
                             ->andWhere("{$alias}.image != ''");
                     } elseif ($value == self::FILTER_WITHOUT_IMAGE_ONLY) {
                         $queryBuilder
                             ->andWhere("{$alias}.image = ''");
                     }

                },
                'type' => ChoiceType::class,
                'default' => 'Any',
                'options' => [
                    'choices' => self::IMAGES,
                    'expanded' => true
                ]
            ],
            'createdAt' => [
                'title' => 'Created At'
            ],

            'updatedAt' => [
                'title' => 'Updated At'
            ],

            'status' => [
                'type' => ChoiceType::class,
                'query_builder' => function (QueryBuilder $queryBuilder) {

                },
                'default' => self::ITEM_ANY,
                'options' => [
                    'choices' => [
                        'Any' => self::ITEM_ANY,
                        'Active' => StatusInterface::STATUS_ACTIVE,
                        'Inactive' => StatusInterface::STATUS_INACTIVE
                    ],
                ]
            ]
        ];
    }

    public function getImage()
    {
        return $this->image;
    }

    public function setImage($image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getName()
    {
        return $this->name;
    }

    public function setName( $name = ''): self
    {
        $this->name = $name;
        return $this;
    }

    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    public function setCreatedAt($createdAt): self
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getStatus()
    {
        return $this->status;
    }

    public function setStatus($status)
    {
        $this->status = $status;
        return $this;
    }
}
