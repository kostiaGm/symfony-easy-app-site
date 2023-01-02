<?php

namespace App\Entity\Interfaces;

interface PreviewBodyTextInterface
{
    public function getPreview(): ?string;
    public function setPreview(string $preview): self;
    public function getBody(): ?string;
    public function setBody(?string $body): self;
}