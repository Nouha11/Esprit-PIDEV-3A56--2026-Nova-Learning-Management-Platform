<?php

namespace App\Entity\StudySession;

use App\Entity\users\User;
use App\Repository\StudySession\StudySessionRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: StudySessionRepository::class)]
class StudySession
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'studySessions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\ManyToOne(inversedBy: 'studySessions')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Planning $planning = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $startedAt;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $endedAt = null;

    #[ORM\Column]
    private ?int $duration = null;

    #[ORM\Column(nullable: true)]
    private ?int $actualDuration = null;

    #[ORM\Column(nullable: true)]
    private ?int $energyUsed = null;

    #[ORM\Column(nullable: true)]
    private ?int $xpEarned = null;

    #[ORM\Column(length: 255)]
    private ?string $burnoutRisk = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $completedAt = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $mood = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $energyLevel = null;

    #[ORM\Column(nullable: true)]
    private ?int $breakDuration = null;

    #[ORM\Column(nullable: true)]
    private ?int $breakCount = null;

    #[ORM\Column(nullable: true)]
    private ?int $pomodoroCount = null;

    #[ORM\ManyToMany(targetEntity: Tag::class, inversedBy: 'studySessions')]
    #[ORM\JoinTable(name: 'study_session_tag')]
    private Collection $tags;

    #[ORM\OneToMany(targetEntity: Note::class, mappedBy: 'studySession', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $notes;

    #[ORM\OneToMany(targetEntity: Resource::class, mappedBy: 'studySession', cascade: ['persist', 'remove'], orphanRemoval: true)]
    private Collection $resources;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->notes = new ArrayCollection();
        $this->resources = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getPlanning(): ?Planning
    {
        return $this->planning;
    }

    public function setPlanning(?Planning $planning): static
    {
        $this->planning = $planning;

        return $this;
    }

    public function getStartedAt(): ?\DateTimeImmutable
    {
        return $this->startedAt;
    }

    public function setStartedAt(\DateTimeImmutable $startedAt): static
    {
        $this->startedAt = $startedAt;

        return $this;
    }

    public function getEndedAt(): ?\DateTimeImmutable
    {
        return $this->endedAt;
    }

    public function setEndedAt(?\DateTimeImmutable $endedAt): static
    {
        $this->endedAt = $endedAt;

        return $this;
    }

    public function getDuration(): ?int
    {
        return $this->duration;
    }

    public function setDuration(int $duration): static
    {
        $this->duration = $duration;

        return $this;
    }

    public function getActualDuration(): ?int
    {
        return $this->actualDuration;
    }

    public function setActualDuration(?int $actualDuration): static
    {
        $this->actualDuration = $actualDuration;

        return $this;
    }

    public function getEnergyUsed(): ?int
    {
        return $this->energyUsed;
    }

    public function setEnergyUsed(?int $energyUsed): static
    {
        $this->energyUsed = $energyUsed;

        return $this;
    }

    public function getXpEarned(): ?int
    {
        return $this->xpEarned;
    }

    public function setXpEarned(?int $xpEarned): static
    {
        $this->xpEarned = $xpEarned;

        return $this;
    }

    public function getBurnoutRisk(): ?string
    {
        return $this->burnoutRisk;
    }

    public function setBurnoutRisk(string $burnoutRisk): static
    {
        $this->burnoutRisk = $burnoutRisk;

        return $this;
    }

    public function getCompletedAt(): ?\DateTimeImmutable
    {
        return $this->completedAt;
    }

    public function setCompletedAt(?\DateTimeImmutable $completedAt): static
    {
        $this->completedAt = $completedAt;

        return $this;
    }

    public function getMood(): ?string
    {
        return $this->mood;
    }

    public function setMood(?string $mood): static
    {
        $this->mood = $mood;

        return $this;
    }

    public function getEnergyLevel(): ?string
    {
        return $this->energyLevel;
    }

    public function setEnergyLevel(?string $energyLevel): static
    {
        $this->energyLevel = $energyLevel;

        return $this;
    }

    public function getBreakDuration(): ?int
    {
        return $this->breakDuration;
    }

    public function setBreakDuration(?int $breakDuration): static
    {
        $this->breakDuration = $breakDuration;

        return $this;
    }

    public function getBreakCount(): ?int
    {
        return $this->breakCount;
    }

    public function setBreakCount(?int $breakCount): static
    {
        $this->breakCount = $breakCount;

        return $this;
    }

    public function getPomodoroCount(): ?int
    {
        return $this->pomodoroCount;
    }

    public function setPomodoroCount(?int $pomodoroCount): static
    {
        $this->pomodoroCount = $pomodoroCount;

        return $this;
    }

    /**
     * @return Collection<int, Tag>
     */
    public function getTags(): Collection
    {
        return $this->tags;
    }

    public function addTag(Tag $tag): static
    {
        if (!$this->tags->contains($tag)) {
            $this->tags->add($tag);
        }

        return $this;
    }

    public function removeTag(Tag $tag): static
    {
        $this->tags->removeElement($tag);

        return $this;
    }

    /**
     * @return Collection<int, Note>
     */
    public function getNotes(): Collection
    {
        return $this->notes;
    }

    public function addNote(Note $note): static
    {
        if (!$this->notes->contains($note)) {
            $this->notes->add($note);
            $note->setStudySession($this);
        }

        return $this;
    }

    public function removeNote(Note $note): static
    {
        if ($this->notes->removeElement($note)) {
            if ($note->getStudySession() === $this) {
                $note->setStudySession(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Resource>
     */
    public function getResources(): Collection
    {
        return $this->resources;
    }

    public function addResource(Resource $resource): static
    {
        if (!$this->resources->contains($resource)) {
            $this->resources->add($resource);
            $resource->setStudySession($this);
        }

        return $this;
    }

    public function removeResource(Resource $resource): static
    {
        if ($this->resources->removeElement($resource)) {
            if ($resource->getStudySession() === $this) {
                $resource->setStudySession(null);
            }
        }

        return $this;
    }
}
