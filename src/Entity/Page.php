<?php

namespace App\Entity;

use App\Entity\Interfaces\ChangeDataDayInterface;
use App\Entity\Interfaces\ImageInterface;
use App\Entity\Interfaces\IsJoinMenuInterface;
use App\Entity\Interfaces\IsOnMainPageInterface;
use App\Entity\Interfaces\OwnerInterface;
use App\Entity\Interfaces\PreviewBodyTextInterface;
use App\Entity\Interfaces\SeoInterface;
use App\Entity\Interfaces\SiteInterface;
use App\Entity\Interfaces\StatusInterface;
use App\Entity\Traits\ChangeDataDayTrait;
use App\Entity\Traits\ImageTrait;
use App\Entity\Traits\IsJoinMenuTrait;
use App\Entity\Traits\IsOnMainPageTrait;
use App\Entity\Traits\OwnerTrait;
use App\Entity\Traits\PreviewBodyTextTrait;
use App\Entity\Traits\SeoTrait;
use App\Entity\Traits\SiteTrait;
use App\Entity\Traits\StatusTrait;
use App\Repository\PageRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PageRepository::class)
 * @ORM\Table(
 *     indexes={
 *          @ORM\Index(
 *                  name="is_on_main_page", columns={"is_on_main_page"},
 *                  name="site_id", columns={"site_id"},
 *                  name="menu_id", columns={"menu_id"}
 *              )
 *          }
 *     )
 */
class Page implements
    ChangeDataDayInterface,
    PreviewBodyTextInterface,
    StatusInterface,
    IsOnMainPageInterface,
    IsJoinMenuInterface,
    SiteInterface,
    ImageInterface,
    OwnerInterface
{
    use ChangeDataDayTrait,
        PreviewBodyTextTrait,
        StatusTrait,
        IsOnMainPageTrait,
        IsJoinMenuTrait,
        SiteTrait,
        ImageTrait,
        OwnerTrait

        ;
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $name;

    /**
     * @ORM\ManyToOne(targetEntity=Menu::class, inversedBy="pages")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     *
     */
    private $menu;

    /**
     * @ORM\Column(type="smallint", nullable=true)
     */
    private $previewDeep;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isPreview;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getRenderPageRoute(): string
    {
        return 'app_page_detail';
    }

    public function getPreviewDeep(): ?int
    {
        return $this->previewDeep;
    }

    public function setPreviewDeep(?int $previewDeep): self
    {
        $this->previewDeep = $previewDeep;

        return $this;
    }

    public function isIsPreview(): ?bool
    {
        return $this->isPreview;
    }

    public function setIsPreview(bool $isPreview): self
    {
        $this->isPreview = $isPreview;

        return $this;
    }

}

