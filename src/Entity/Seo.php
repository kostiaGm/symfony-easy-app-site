<?php

namespace App\Entity;

use App\Repository\SeoRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @UniqueEntity(
 *     fields={"entity", "entityId"},
 *     errorPath="name"
 * )
 *
 * @ORM\Table(
 *     indexes={
 *          @ORM\Index(
 *                  name="entity_entity_id_site_id", columns={"site_id", "entity_id", "entity"}
 *              )
 *          }
 *     )
 * @ORM\Entity(repositoryClass=SeoRepository::class)
 */
class Seo
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $entity;

    /**
     * @ORM\Column(type="integer")
     */
    private $entityId;

    /**
     * @ORM\Column(type="integer")
     */
    private $siteId;

    /**
     * @ORM\OneToMany(targetEntity=SeoItem::class, mappedBy="seo", cascade={"persist"})
     */
    private $items;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEntity(): ?string
    {
        return $this->entity;
    }

    public function setEntity(string $entity): self
    {
        $this->entity = $entity;

        return $this;
    }

    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    public function setEntityId(int $entityId): self
    {
        $this->entityId = $entityId;

        return $this;
    }

    public function getSiteId(): ?int
    {
        return $this->siteId;
    }

    public function setSiteId(int $siteId): self
    {
        $this->siteId = $siteId;

        return $this;
    }

    /**
     * @return Collection<int, SeoItem>
     */
    public function getItems(): Collection
    {
        return $this->items;
    }

    public function addItem(SeoItem $item): self
    {
        if (!$this->items->contains($item)) {
            $this->items[] = $item;
            $item->setSeo($this);
        }

        return $this;
    }

    public function removeItem(SeoItem $item): self
    {
        if ($this->items->removeElement($item)) {
            // set the owning side to null (unless already changed)
            if ($item->getSeo() === $this) {
                $item->setSeo(null);
            }
        }

        return $this;
    }
}
