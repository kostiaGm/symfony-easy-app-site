<?php

namespace App\Entity;

use App\Entity\Interfaces\FilePathInterface;
use App\Entity\Interfaces\IsDefaultInterface;
use App\Entity\Interfaces\NameInterface;
use App\Entity\Interfaces\SizeInterface;
use App\Entity\Traits\FilePathTrait;
use App\Entity\Traits\IsDefaultTrait;
use App\Entity\Traits\NameTrait;
use App\Entity\Traits\SizeTrait;
use App\Repository\GallerySettingRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GallerySettingRepository::class)
 */
class GallerySetting implements
    NameInterface,
    SizeInterface,
    FilePathInterface,
    IsDefaultInterface
{
    use
        NameTrait,
        SizeTrait,
        FilePathTrait,
        IsDefaultTrait
        ;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Gallery::class, inversedBy="settings")
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

