<?php

namespace App\Entity;

use App\Entity\Interfaces\AuthorInterface;
use App\Entity\Interfaces\DescriptionInterface;
use App\Entity\Interfaces\IsDefaultInterface;
use App\Entity\Interfaces\NameInterface;
use App\Entity\Interfaces\OwnerInterface;
use App\Entity\Interfaces\PermissionInterface;
use App\Entity\Interfaces\SafeDeleteInterface;
use App\Entity\Interfaces\StatusInterface;
use App\Entity\Traits\AuthorTrait;
use App\Entity\Traits\DescriptionTrait;
use App\Entity\Traits\IsDefaultTrait;
use App\Entity\Traits\NameTrait;
use App\Entity\Traits\OwnerTrait;
use App\Entity\Traits\PermissionTrait;
use App\Entity\Traits\SafeDeleteTrait;
use App\Entity\Traits\StatusTrait;
use App\Repository\GalleryRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=GalleryRepository::class)
 */
class Gallery implements
    NameInterface,
    DescriptionInterface,
    OwnerInterface,
    AuthorInterface,
    PermissionInterface,
    SafeDeleteInterface,
    StatusInterface
{

    use
        NameTrait,
        DescriptionTrait,
        OwnerTrait,
        AuthorTrait,
        PermissionTrait,
        SafeDeleteTrait,
        StatusTrait
        ;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    private $images;

    /**
     * @ORM\OneToMany(targetEntity=GallerySetting::class, mappedBy="gallery")
     */
    private $settings;


    public function __construct()
    {
        $this->images = new ArrayCollection();
        $this->settings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getImages(): Collection
    {
        return $this->images;
    }


    public function addImage(Image $image): self
    {
        if (!$this->images->contains($image)) {
            $this->images[] = $image;
            $image->setGallery($this);
        }

        return $this;
    }

    public function removeImage(Image $image): self
    {
        if ($this->images->removeElement($image)) {
            if ($image->getGallery() === $this) {
                $image->setGallery(null);
            }
        }
        return $this;
    }


    /**
     * @return Collection<int, GallerySetting>
     */
    public function getSettings(): Collection
    {
        return $this->settings;
    }

    public function addSetting(GallerySetting $setting): self
    {
        if (!$this->settings->contains($setting)) {
            $this->settings[] = $setting;
            $setting->setGallery($this);
        }

        return $this;
    }

    public function removeSetting(GallerySetting $setting): self
    {
        if ($this->settings->removeElement($setting)) {
            // set the owning side to null (unless already changed)
            if ($setting->getGallery() === $this) {
                $setting->setGallery(null);
            }
        }

        return $this;
    }
}
