<?php

namespace App\Entity\Interfaces;

interface SeoInterface
{
    public function getSeoTitle(): ?string;

    public function setSeoTitle(?string $seoTitle): self;

    public function getSeoDescription(): ?string;

    public function setSeoDescription(?string $seoDescription): self;

    public function getSeoKeywords(): ?string;

    public function setSeoKeywords(?string $seoKeywords): self;
}