<?php

namespace App\Entity\Interfaces;

use App\Entity\User;

interface AuthorInterface
{
    public function getAuthor(): ?User;

    public function seAuthor(?User $author): self;
}