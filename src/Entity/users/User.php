<?php

namespace App\Entity;

use App\Entity\StudySession\StudySession;
use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    private ?string $email = null;

    #[ORM\Column(length: 255)]
    private ?string $password = null;

    #[ORM\Column(length: 100)]
    private ?string $username = null;

    #[ORM\Column(length: 50)]
    private ?string $role = null;

    #[ORM\Column]
    private ?bool $isActive = null;

    /**
     * @var Collection<int, StudySession>
     */
    #[ORM\OneToMany(targetEntity: StudySession::class, mappedBy: 'user')]
    private Collection $studySessions;

    public function __construct()
    {
        $this->studySessions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(string $role): static
    {
        $this->role = $role;

        return $this;
    }

    public function isActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): static
    {
        $this->isActive = $isActive;

        return $this;
    }

    /**
     * @return Collection<int, StudySession>
     */
    public function getStudySessions(): Collection
    {
        return $this->studySessions;
    }

    public function addStudySession(StudySession $studySession): static
    {
        if (!$this->studySessions->contains($studySession)) {
            $this->studySessions->add($studySession);
            $studySession->setUser($this);
        }

        return $this;
    }

    public function removeStudySession(StudySession $studySession): static
    {
        if ($this->studySessions->removeElement($studySession)) {
            // set the owning side to null (unless already changed)
            if ($studySession->getUser() === $this) {
                $studySession->setUser(null);
            }
        }

        return $this;
    }
}
