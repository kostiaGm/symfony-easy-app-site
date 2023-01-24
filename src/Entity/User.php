<?php

namespace App\Entity;

use App\Entity\Interfaces\SafeDeleteInterface;
use App\Entity\Interfaces\SiteInterface;
use App\Entity\Interfaces\StatusInterface;
use App\Entity\Traits\SafeDeleteTrait;
use App\Entity\Traits\SiteTrait;
use App\Entity\Traits\StatusTrait;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @ORM\Entity(repositoryClass=UserRepository::class)
 * @UniqueEntity(fields={"username"}, message="There is already an account with this username")
 */
class User implements
    UserInterface,
    PasswordAuthenticatedUserInterface,
    StatusInterface,
    SafeDeleteInterface
{
    use
        SiteTrait,
        StatusTrait,
        SafeDeleteTrait
        ;

    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=180, unique=true)
     */
    private $username;

    /**
     * @var string The hashed password
     * @ORM\Column(type="string")
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $email;

    /**
     * @ORM\Column(type="integer")
     */
    private $siteId;

    /**
     * @ORM\ManyToMany(targetEntity=Role::class, inversedBy="users")
     */
    private $roles;

    private $otherUserIdsWithMyGroups = [];


    public function __construct()
    {
        $this->roles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @deprecated since Symfony 5.3, use getUserIdentifier instead
     */

    public function getUsername(): string
    {
        return (string) $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getRoles()
    {
        $result = [];
        foreach ($this->getRolesCollection()->toArray() as $item) {
            $result[] = $item->getRole();
        }
        return $result;
    }

    public function getRolesCollection(): Collection
    {
        return $this->roles;
    }


    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(?string $password): self
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Returning a salt is only needed, if you are not using a modern
     * hashing algorithm (e.g. bcrypt or sodium) in your security.yaml.
     *
     * @see UserInterface
     */
    public function getSalt(): ?string
    {
        return null;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials()
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

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

    public function addRole(Role $role): self
    {
        if (!$this->roles->contains($role)) {
            $this->roles[] = $role;
        }

        return $this;
    }

    public function removeRole(Role $role): self
    {
        $this->roles->removeElement($role);

        return $this;
    }

    /**
     * @return array
     */
    public function getOtherUserIdsWithMyGroups(): array
    {
        return $this->otherUserIdsWithMyGroups;
    }

    /**
     * @param array $otherUserIdsWithMyGroups
     * @return User
     */
    public function setOtherUserIdsWithMyGroups(array $otherUserIdsWithMyGroups): User
    {
        $this->otherUserIdsWithMyGroups = $otherUserIdsWithMyGroups;
        return $this;
    }
}
