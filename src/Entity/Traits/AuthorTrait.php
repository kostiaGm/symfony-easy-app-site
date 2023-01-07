<?php

namespace App\Entity\Traits;

use App\Entity\User;

trait AuthorTrait
{
    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private $author;

    public function getAuthor(): ?User
    {
        return $this->author;
    }

    public function seAuthor(?User $author): self
    {
        $this->author = $author;

        return $this;
    }
}