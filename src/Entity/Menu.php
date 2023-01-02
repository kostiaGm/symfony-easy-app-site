<?php

namespace App\Entity;

use App\Entity\Interfaces\ChangeDataDayInterface;
use App\Entity\Interfaces\JoinSiteInterface;
use App\Entity\Interfaces\NodeInterface;
use App\Entity\Interfaces\StatusInterface;
use App\Entity\Traits\ChangeDataDayTrait;
use App\Entity\Traits\NodeTrait;
use App\Entity\Traits\StatusTrait;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Repository\MenuRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=MenuRepository::class)
 * @UniqueEntity(
 *     fields={"name", "tree"},
 *     errorPath="name"
 * )
 *
 * @ORM\Table(
 *     indexes={
 *          @ORM\Index(
 *                  name="lft", columns={"lft"},
 *                  name="lft_rgt", columns={"lft", "rgt"},
 *                  name="id_lft_rgt", columns={"id", "lft", "rgt"},
 *                  name="path", columns={"site_id","path"}
 *              )
 *          }
 *     )
 */
class Menu implements NodeInterface, StatusInterface, ChangeDataDayInterface, JoinSiteInterface
{
    public const SITE_PAGE_TYPE = 1;
    public const EXTERNAL_PAGE_TYPE = 2;
    public const SUB_ITEM_MENU_TYPE = 3;

    public const TYPES = [
        self::SITE_PAGE_TYPE => 'Site page',
        self::EXTERNAL_PAGE_TYPE => 'External page',
        self::SUB_ITEM_MENU_TYPE => 'Sub item menu',
    ];

    use StatusTrait, ChangeDataDayTrait, NodeTrait;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private ?int $id = null;

    /** @ORM\Column(type="string", nullable=true) */
    private ?string $route = null;

    /** @ORM\Column(type="string", nullable=true) */
    private ?string $url = null;

    /** @ORM\Column(type="string", nullable=true) */
    private ?string $path = null;

    /** @ORM\Column(type="smallint", nullable=true) */
    private ?int $type = null;

    /**
     * @ORM\ManyToOne(targetEntity=Site::class, inversedBy="menus")
     */
    private ?Site $site = null;

    /**
     * @ORM\OneToMany(targetEntity=Page::class, mappedBy="menu")
     */
    private $pages;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $entityId;

    public function __construct()
    {
        $this->pages = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function setRoute(?string $route): self
    {
        $this->route = $route;
        return $this;
    }

    public function getUrl(): ?string
    {
        return $this->url;
    }

    public function setUrl(?string $url): self
    {
        $this->url = $url;
        return $this;
    }

    public function getPath(): ?string
    {
        return stripslashes($this->path);
    }

    public function setPath(?string $path): self
    {
        $path = htmlspecialchars(addslashes($path));
        $this->path = $path;
        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(?int $type): self
    {
        $this->type = $type;
        return $this;
    }

    public function getTransliteratedUrl(): string
    {
        $transliteratorAny = \Transliterator::create('Any-Latin');
        $return = trim($this->getUrl() ?? $this->getName());
        $return = $transliteratorAny->transliterate($return);
        $return = preg_replace('/\W+/', '-', $return);
        $return = strtolower($return);
        return $return;
    }

    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): self
    {
        $this->site = $site;

        return $this;
    }

    /**
     * @return Collection<int, Page>
     */
    public function getPages(): Collection
    {
        return $this->pages;
    }

    public function addPage(Page $page): self
    {
        if (!$this->pages->contains($page)) {
            $this->pages[] = $page;
            $page->setMenu($this);
        }

        return $this;
    }

    public function removePage(Page $page): self
    {
        if ($this->pages->removeElement($page)) {
            // set the owning side to null (unless already changed)
            if ($page->getMenu() === $this) {
                $page->setMenu(null);
            }
        }
        return $this;
    }


    public function getEntityId(): ?int
    {
        return $this->entityId;
    }

    public function setEntityId(?int $entityId): self
    {
        $this->entityId = $entityId;

        return $this;
    }
}