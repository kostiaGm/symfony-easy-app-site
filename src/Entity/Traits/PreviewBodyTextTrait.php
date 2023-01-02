<?php

namespace App\Entity\Traits;

trait PreviewBodyTextTrait
{
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $preview = null;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $body = null;

    public function getPreview(): ?string
    {
        return $this->preview;
    }

    public function setPreview(?string $preview): self
    {
        $this->preview = $preview;

        return $this;
    }

    public function getBody(): ?string
    {
        return $this->body;
    }

    public function setBody(?string $body): self
    {
        $this->body = $body;

        return $this;
    }
}