<?php

namespace App\Entity;

use App\Entity\Interfaces\DescriptionInterface;
use App\Entity\Interfaces\ImageInterface;
use App\Entity\Interfaces\NameInterface;
use App\Entity\Interfaces\OwnerInterface;
use App\Entity\Interfaces\SiteInterface;
use App\Entity\Interfaces\SizeInterface;
use App\Entity\Traits\DescriptionTrait;
use App\Entity\Traits\ImageTrait;
use App\Entity\Traits\NameTrait;
use App\Entity\Traits\OwnerTrait;
use App\Entity\Traits\SiteTrait;
use App\Entity\Traits\SizeTrait;
use App\Repository\ImageRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=ImageRepository::class)
 */
class Image implements
    NameInterface,
    DescriptionInterface,
    OwnerInterface,
    SizeInterface,
    ImageInterface
{
    use
        NameTrait,
        DescriptionTrait,
        OwnerTrait,
        SizeTrait,
        ImageTrait
        ;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Gallery::class, inversedBy="images")
     */
    private $gallery;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getGallery(): ?Gallery
    {
        return $this->gallery;
    }

    public function setGallery(?Gallery $gallery): self
    {
        $this->gallery = $gallery;

        return $this;
    }
}
