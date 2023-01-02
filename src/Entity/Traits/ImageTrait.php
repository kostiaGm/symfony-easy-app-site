<?php

namespace App\Entity\Traits;

trait ImageTrait
{
    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $image;

    private $uploadImage;

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getUploadImage(): ?string
    {
        return $this->uploadImage;
    }


    public function setUploadImage(?string $uploadImage)
    {
        $this->uploadImage = $uploadImage;
        return $this;
    }


}