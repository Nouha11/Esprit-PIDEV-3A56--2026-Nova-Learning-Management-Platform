<?php

namespace App\Entity\StudySession;

use App\Repository\StudySession\PlanningRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: PlanningRepository::class)]
class Planning
{
    public const STATUS_SCHEDULED = 'SCHEDULED';
    public const STATUS_COMPLETED = 'COMPLETED';
    public const STATUS_MISSED = 'MISSED';
    public const STATUS_CANCELLED = 'CANCELLED';
    
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

        // Planning.php
    #[Assert\NotNull(message: "A course must be selected")]
    #[ORM\ManyToOne(inversedBy: 'plannings')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Course $course = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Title is required")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Title must be at least 3 characters",
        maxMessage: "Title cannot exceed 255 characters"
    )]
    private ?string $title = null;

    #[ORM\Column(type: Types::DATE_IMMUTABLE)]
    #[Assert\NotNull(message: "Scheduled date is required")]
    #[Assert\GreaterThanOrEqual(
        "today",
        message: "Scheduled date cannot be in the past"
    )]
    private ?\DateTimeImmutable $scheduledDate = null;

    #[ORM\Column(type: Types::TIME_IMMUTABLE)]
    #[Assert\NotNull(message: "Scheduled time is required")]
    private ?\DateTimeImmutable $scheduledTime = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Planned duration is required")]
    #[Assert\Positive(message: "Planned duration must be positive")]
    private ?int $plannedDuration = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Status is required")]
    #[Assert\Choice(
        choices: ['SCHEDULED', 'COMPLETED', 'MISSED', 'CANCELLED'],
        message: "Choose a valid planning status"
    )]
    private ?string $status = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Reminder value is required")]
    private ?bool $reminder = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, StudySession>
     */
    #[ORM\OneToMany(targetEntity: StudySession::class, mappedBy: 'planning')]
    private Collection $studySessions;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->status = 'SCHEDULED';
        $this->reminder = false;
        $this->studySessions = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getScheduledDate(): ?\DateTimeImmutable
    {
        return $this->scheduledDate;
    }

    public function setScheduledDate(\DateTimeImmutable $scheduledDate): static
    {
        $this->scheduledDate = $scheduledDate;

        return $this;
    }

    public function getScheduledTime(): ?\DateTimeImmutable
    {
        return $this->scheduledTime;
    }

    public function setScheduledTime(\DateTimeImmutable $scheduledTime): static
    {
        $this->scheduledTime = $scheduledTime;

        return $this;
    }

    public function getPlannedDuration(): ?int
    {
        return $this->plannedDuration;
    }

    public function setPlannedDuration(int $plannedDuration): static
    {
        $this->plannedDuration = $plannedDuration;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

    public function isReminder(): ?bool
    {
        return $this->reminder;
    }

    public function setReminder(bool $reminder): static
    {
        $this->reminder = $reminder;

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

    public function getCourse(): ?Course
    {
        return $this->course;
    }

    public function setCourse(?Course $course): static
    {
        $this->course = $course;
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
            $studySession->setPlanning($this);
        }

        return $this;
    }

    public function removeStudySession(StudySession $studySession): static
    {
        if ($this->studySessions->removeElement($studySession)) {
            // set the owning side to null (unless already changed)
            if ($studySession->getPlanning() === $this) {
                $studySession->setPlanning(null);
            }
        }

        return $this;
    }
}
