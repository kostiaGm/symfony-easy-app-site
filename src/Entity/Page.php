<?php

namespace App\Entity;

use App\Entity\Interfaces\ChangeDataDayInterface;
use App\Entity\Interfaces\ImageInterface;
use App\Entity\Interfaces\IsJoinMenuInterface;
use App\Entity\Interfaces\IsOnMainPageInterface;
use App\Entity\Interfaces\PreviewBodyTextInterface;
use App\Entity\Interfaces\SiteInterface;
use App\Entity\Interfaces\StatusInterface;
use App\Entity\Traits\ChangeDataDayTrait;
use App\Entity\Traits\ImageTrait;
use App\Entity\Traits\IsJoinMenuTrait;
use App\Entity\Traits\IsOnMainPageTrait;
use App\Entity\Traits\PreviewBodyTextTrait;
use App\Entity\Traits\SiteTrait;
use App\Entity\Traits\StatusTrait;
use App\Repository\PageRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PageRepository::class)
 */
class Page implements
    ChangeDataDayInterface,
    PreviewBodyTextInterface,
    StatusInterface,
    IsOnMainPageInterface,
    IsJoinMenuInterface,
    SiteInterface,
    ImageInterface
{
    use ChangeDataDayTrait, PreviewBodyTextTrait, StatusTrait, IsOnMainPageTrait, IsJoinMenuTrait, SiteTrait, ImageTrait;
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
     */
    private $menu;

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
}
