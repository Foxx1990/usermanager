<?php

namespace App\Entity;

use ApiPlatform\Core\Annotation\ApiResource;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ApiResource
 */
class User implements UserInterface,PasswordAuthenticatedUserInterface
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
    private $id;

    /** @ORM\Column(type="string", length=180, unique=true) */
    private $email;

    /** @ORM\Column(type="json") */
    private $roles = [];

    /** @ORM\Column(type="string") */
    private $password;

    /** @ORM\Column(type="string", length=255) */
    private $firstName;

    /** @ORM\Column(type="string", length=255) */
    private $lastName;

    /** @ORM\Column(type="boolean") */
    private $isActive = true;

    public function getId(): ?int
    {
        return $this->id;
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

    public function getRoles(): array
    {
        $roles = $this->roles;
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getSalt()
    {
        // not needed for bcrypt or argon
    }

    public function eraseCredentials()
    {
        // clear sensitive data
    }

    public function getUserIdentifier():string
    {}

}
