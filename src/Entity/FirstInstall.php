<?php

namespace App\Entity;

class FirstInstall
{
    private ?Site $site = null;

    private ?User $user = null;

    /**
     * @return Site
     */
    public function getSite(): ?Site
    {
        return $this->site;
    }

    /**
     * @param Site $site
     * @return FirstInstall
     */
    public function setSite(Site $site): self
    {
        $this->site = $site;
        return $this;
    }

    /**
     * @return User
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return FirstInstall
     */
    public function setUser(User $user): self
    {
        $this->user = $user;
        return $this;
    }


}