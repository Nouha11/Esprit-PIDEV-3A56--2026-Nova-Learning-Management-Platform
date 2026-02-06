<?php

namespace App\Entity\StudySession;

use App\Repository\StudySession\CourseRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;


#[ORM\Entity(repositoryClass: CourseRepository::class)]
class Course
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    // Course.php relation OneToMany
    #[ORM\OneToMany(mappedBy: 'course', targetEntity: Planning::class)]
    private Collection $plannings;


    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Course name is required")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Course name must be at least 3 characters",
        maxMessage: "Course name cannot exceed 255 characters"
    )]
    private ?string $courseName = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        max: 255,
        maxMessage: "Description cannot exceed 255 characters"
    )]
    private ?string $description = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Difficulty is required")]
    #[Assert\Choice(
        choices: ['BEGINNER', 'INTERMEDIATE', 'ADVANCED'],
        message: "Choose a valid difficulty level"
    )]
    private ?string $difficulty = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Estimated duration is required")]
    #[Assert\Positive(message: "Estimated duration must be positive")]
    private ?int $estimatedDuration = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Range(
        min: 0,
        max: 100,
        notInRangeMessage: "Progress must be between {{ min }}% and {{ max }}%"
    )]
    private ?int $progress = 0;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Status is required")]
    #[Assert\Choice(
        choices: ['NOT_STARTED', 'IN_PROGRESS', 'COMPLETED'],
        message: "Choose a valid course status"
    )]
    private ?string $status = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Category is required")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Category must be at least 3 characters",
        maxMessage: "Category cannot exceed 255 characters"
    )]
    private ?string $category = null;

    #[ORM\Column(nullable: true)]
    #[Assert\Positive(message: "Maximum students must be positive")]
    private ?int $maxStudents = null;

    #[ORM\Column]
    private ?bool $isPublished = false;

    public function __construct()
    {
        $this->createdAt = new \DateTimeImmutable();
        $this->progress = 0;
        $this->isPublished = false;
        $this->status = 'NOT_STARTED';
        $this->plannings = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCourseName(): ?string
    {
        return $this->courseName;
    }

    public function setCourseName(string $courseName): static
    {
        $this->courseName = $courseName;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getDifficulty(): ?string
    {
        return $this->difficulty;
    }

    public function setDifficulty(string $difficulty): static
    {
        $this->difficulty = $difficulty;

        return $this;
    }

    public function getEstimatedDuration(): ?int
    {
        return $this->estimatedDuration;
    }

    public function setEstimatedDuration(int $estimatedDuration): static
    {
        $this->estimatedDuration = $estimatedDuration;

        return $this;
    }

    public function getProgress(): ?int
    {
        return $this->progress;
    }

    public function setProgress(?int $progress): static
    {
        $this->progress = $progress;

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

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    public function getCategory(): ?string
    {
        return $this->category;
    }

    public function setCategory(string $category): static
    {
        $this->category = $category;

        return $this;
    }

    public function getMaxStudents(): ?int
    {
        return $this->maxStudents;
    }

    public function setMaxStudents(?int $maxStudents): static
    {
        $this->maxStudents = $maxStudents;

        return $this;
    }

    public function isPublished(): ?bool
    {
        return $this->isPublished;
    }

    public function setIsPublished(bool $isPublished): static
    {
        $this->isPublished = $isPublished;

        return $this;
    }

    /**
     * @return Collection<int, Planning>
     */
    public function getPlannings(): Collection
    {
        return $this->plannings;
    }

    public function addPlanning(Planning $planning): static
    {
        if (!$this->plannings->contains($planning)) {
            $this->plannings->add($planning);
            $planning->setCourse($this);
        }

        return $this;
    }

    public function removePlanning(Planning $planning): static
    {
        if ($this->plannings->removeElement($planning)) {
            if ($planning->getCourse() === $this) {
                $planning->setCourse(null);
            }
        }

        return $this;
    }


}
