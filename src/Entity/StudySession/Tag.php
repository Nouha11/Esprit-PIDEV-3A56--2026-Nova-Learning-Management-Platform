<?php

namespace App\Entity\StudySession;

use App\Repository\StudySession\TagRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: TagRepository::class)]
#[ORM\Table(name: 'tag')]
#[ORM\Index(name: 'idx_name', columns: ['name'])]
class Tag
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50, unique: true)]
    private ?string $name = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToMany(targetEntity: StudySession::class, mappedBy: 'tags')]
    private Collection $studySessions;

    public function __construct()
    {
        $this->studySessions = new ArrayCollection();
        $this->createdAt = new \DateTimeImmutable();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

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
            $studySession->addTag($this);
        }

        return $this;
    }

    public function removeStudySession(StudySession $studySession): static
    {
        if ($this->studySessions->removeElement($studySession)) {
            $studySession->removeTag($this);
        }

        return $this;
    }
}
